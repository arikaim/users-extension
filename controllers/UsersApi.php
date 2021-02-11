<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Extensions\Users\Controllers;

use Arikaim\Core\Db\Model;
use Arikaim\Core\Controllers\ApiController;
use Arikaim\Core\Http\Url;
use Arikaim\Core\Utils\Utils;
use Arikaim\Core\Http\Cookie;
use Arikaim\Core\Http\Session;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Utils\Text;
use Arikaim\Core\View\Html\Page;

use Arikaim\Core\Controllers\Traits\AccessToken;
use Arikaim\Core\Controllers\Traits\Captcha;
use Arikaim\Extensions\Users\Controllers\Traits\Users;

/**
 * Users api controller
*/
class UsersApi extends ApiController
{
    use 
        AccessToken,
        Users,
        Captcha;

    /**
     * Init controller
     *
     * @return void
     */
    public function init()
    {
        $this->loadMessages('users>users.messages');
    }

    /**
     * Signup api
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function signupController($request, $response, $data) 
    {       
        $settings = $this->get('options')->get('users.signup.form');
        $captchaProtect = $settings['captcha']['show'] ?? false;
        if ($captchaProtect == true) {
            $result = $this->verifyCaptcha($request,$data);         
            if ($result == false) {               
                return;
            } 
        }
       
        $this->onDataValid(function($data) use($settings) { 
            $user = $this->userSignup($data,$settings);
            $redirectUrl = $data->get('redirect_url','');
        
            if ($user !== false) {
                // send confirm email to user
                $sendConfirmEmail = (bool)$this->get('options')->get('users.notifications.email.verification',false);
                $emailSend = ($sendConfirmEmail === true) ? $this->sendConfirmationEmail($user->toArray()) : false;
            } else {
                return false;
            }

            $this->setResponse(\is_object($user),function() use($user,$emailSend,$redirectUrl) { 
                if (empty($redirectUrl) == false) {
                    $redirectUrl = Text::render($redirectUrl,['user' => $user->uuid]);
                    $redirectUrl = (Url::isRelative($redirectUrl) == true) ? Page::getUrl($redirectUrl,true) : $redirectUrl;
                }
                $this
                    ->message('signup')
                    ->field('uuid',$user->uuid)
                    ->field('redirect_url',$redirectUrl)
                    ->field('email_send',$emailSend)
                    ->field('status',$user->status);                        
            },'errors.signup');                         
        });  
        $repeatPassword = $data->get('repeat_password');
        $data           
            ->addRule('regexp:exp=/^[A-Za-z][A-Za-z0-9]{2,32}$/|required','user_name',$this->getMessage('errors.username.valid'))       
            ->addRule('text:min=4|required','repeat_password')
            ->addRule('text:min=4|required','password')
            ->addRule('equal:value=' . $repeatPassword . '|required','password',$this->getMessage('errors.repeat_password'));

        if ($settings['name']['required'] == 'true') {
            $data->addRule('text:min=2|required','name');
        }
        if ($settings['phone']['required'] == 'true') {
            $data->addRule('text:min=2|required','phone');
        }
        if ($settings['email']['required'] == 'true') {
            $data->addRule('email:|required','email');
        }
        if ($settings['username']['required'] == 'true') {
            $data->addRule('text:min=2|required','user_name');
        }           
        $data->validate();           
    }

    /**
     * Change user details page
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function changeDetailsController($request, $response, $data) 
    { 
        // get current auth user
        $user = $this->get('access')->getUser();

        $this->onDataValid(function($data) use($user) {
            $userModel = Model::Users()->findById($user['id']);
            // save user 
            $result = $userModel->update([
                'user_name' => $data->getString('user_name',null),
                'email'     => $data->getString('email',null)
            ]);
            if ($result == false) {
                $this->error('errors.update');
                return;
            }
            // save user details
            $result = Model::UserDetails('Users')->saveDetails($user['id'],$data->toArray());          
            $this->setResponse($result,function() use($user) {  
                $this
                    ->message('update')
                    ->field('uuid',$user['uuid']);
            },'errors.update');               
        });         
        $data          
            ->addRule('text:min=2','first_name')
            ->addRule('htmlTags','first_name',$this->getMessage('errors.html'))
            ->addRule('htmlTags','last_name',$this->getMessage('errors.html'))
            ->addRule('unique:model=Users|field=user_name|exclude=' . $user['user_name'],'user_name',$this->getMessage('errors.username.exist'))
            ->addRule('unique:model=Users|field=email|exclude=' . $user['email'],'email',$this->getMessage('errors.email'))
            ->validate();     
    }

    /**
     * User Login
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function loginController($request, $response, $data)
    {       
        $loginAttempts = $this->get('access')->getLoginAttempts();

        if ($loginAttempts > 1) {
            $captchaProtect = $this->get('options')->get('users.login.captcha.protect');
            if ($captchaProtect == true) {      
                if ($this->verifyCaptcha($request,$data) == false) {                                            
                    return;
                }                  
            }
        }
        $loginWith = $this->get('options')->get('users.login.with');

        $this->onDataValid(function($data) use($loginWith,$loginAttempts) {  
            $remember = $data->get('remember',false);
            $credentials = $this->resolveLoginCredentials($loginWith,$data);
            $result = $this->get('access')->authenticate($credentials);
                       
            $this->setResponse($result,function() use ($remember) {  
                $user = $this->get('access')->getUser();  
                Model::Users()->findById($user['uuid'])->updateLoginDate();
                
                if ($remember == true) {
                    // remember user login                                
                    Cookie::add('user',$user['uuid']);
                    $accessToken = $this->get('access')->withProvider('token')->createToken($user['id'],1,4800);   
                    Cookie::add('token',$accessToken['token']);                                  
                } else {       
                    // remove token
                    Cookie::delete('user');
                    Cookie::delete('token');                  
                }

                $redirectUrl = $this->get('options')->get('users.login.redirect',null); 
                $redirectUrl = (empty($redirectUrl) == false) ? Url::BASE_URL . '/' . $redirectUrl : Url::BASE_URL;             
                // dispatch event
                $this->get('event')->dispatch('user.login',$user);
                $this
                    ->message('login')
                    ->field('uuid',$user['uuid'])
                    ->field('redirect_url',$redirectUrl);                           
            },function() use ($loginAttempts) {    
                $this
                    ->error('errors.login')     
                    ->field('attempts',$loginAttempts);                                           
            }); 

        });

        // user name
        if ($loginWith == 1 || $loginWith == 3) {
            $data->addRule('text:min=2|required','user_name');
        }
        // email
        if ($loginWith == 2) {
            $data->addRule('email|required','email');
        }
        $data->validate();               
    }

    /**
     * Reset password
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function resetPasswordController($request, $response, $data)
    {  
        $this->onDataValid(function($data) {
            $user = Model::Users()->findByColumn($data->get('email'),'email');
            if (\is_object($user) == false) {
                $this->error('errors.email.notvalid');
                return;
            }
            $properties = [
                'user'               => $user->toArray(),
                'domain'             => Arikaim::getDomain(),
                'reset_password_url' => $this->createProtectedUrl($user->id,'change-password')
            ];

            $result = $this->get('mailer')->create('users>reset-password',$properties)                
                ->to($user->email)
                ->send();

            $this->setResponse($result,function()  {                        
                $this->message('reset.password.email');                        
            },'errors.reset-password');          

        });
        $data
            ->addRule('exists:model=Users|field=email|required','email',$this->getMessage('errors.not-valid'))
            ->validate();       
    }

    /**
     * Logout
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function logoutController($request, $response, $data)
    {
        $user = $this->get('access')->getUser(); 

        // remove token
        Cookie::delete('user');
        Cookie::delete('token');      

        $this->get('access')->logout();  
        $this->get('cache')->clear();
        
        Session::destroy();

        if (empty($user) == false) {
            // dispatch logout event
            $this->get('event')->dispatch('user.logout',$user);
        }

        $redirectUrl = $this->get('options')->get('users.logout.redirect',''); 
        $redirectUrl = (empty($redirectUrl) == false) ? Url::BASE_URL . '/' . $redirectUrl : Url::BASE_URL;      
        
        $this->field('redirect_url',$redirectUrl);        
    }

    /**
     * Change password
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function changePasswordController($request, $response, $data)
    {      
        $this->onDataValid(function($data) { 
            $password = $data->get('password');
            $user = $this->get('access')->getUser(); 
                 
            if (\is_array($user) === false) {
                $this->error('Access token not valid.');
                return;               
            }
      
            $result = Model::Users()->changePassword($user['id'],$password);       
            $this->setResponse($result,function() use($user) {                         
                $this                    
                    ->message('password')                  
                    ->field('uuid',$user['uuid']);                                     
            },'errors.password');                      
        });

        $repeatPassword = $data->get('repeat_password');
        $data        
            ->addRule('exists:model=Users|field=uuid','uuid')
            ->addRule('text:min=4|required','repeat_password')
            ->addRule('text:min=4|required','password')
            ->addRule('equal:value=' . $repeatPassword . '|required','password','Password and repeat password does not match.')
            ->validate();       
    }

    /**
     * Resolve login credentials
     *
     * @param integer $loginWith
     * @param Collection $data
     * @return array
     */
    protected function resolveLoginCredentials($loginWith, $data)
    {
        $credentials['password'] = $data->get('password');
        switch($loginWith) {
            case 1: 
                $credentials['user_name'] = $data->get('user_name');    
                break;

            case 2: 
                $credentials['email'] = $data->get('email');  
                break;

            case 3: 
                if (Utils::isEmail($data->get('user_name')) == true) {
                    $credentials['email'] = $data->get('user_name');
                } else {
                    $credentials['user_name'] = $data->get('user_name');  
                }
                break;

            default:
                $credentials['user_name'] = $data->get('user_name');
        }

        return $credentials;
    }   
}
