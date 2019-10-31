<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2016-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Extensions\Users\Controllers;

use Arikaim\Core\Db\Model;
use Arikaim\Core\Controllers\ApiController;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Mail\Mail;
use Arikaim\Core\System\Url;
use Arikaim\Core\View\Html\HtmlComponent;

/**
 * Users api controler
*/
class Users extends ApiController
{
    
    public function signup($request, $response, $data) 
    {       
        $settigns = Arikaim::options()->get('users.signup.form');
   
        $this->verifyCaptcha('users.signup.form.captcha.show',$request,$data);          

        $this->onDataValid(function($data) { 
            $activation = Arikaim::options()->get('users.sugnup.activation',1);

            $user = Model::Users()->create($data->toArray());
            if (is_object($user) == false) {
                $this->setError('Error create user');   
            } else {
                Model::UserDetails('users')->saveDetails($user->id,$data->toArray());
                $this->setResult(['message' => 'User created','uuid' => $user->uuid, 'status' => $user->status]);
            } 
        });
        
        $repeat_password = $data->get('repeat_password');
        $data           
            ->addRule('repeat_password','text:min=4|required')
            ->addRule('password','text:min=4|required')
            ->addRule('password','equal:value=' . "$repeat_password|required",'Password and repeat password does not match.');

        if ($settigns['name']['required'] == true) {
           $data->addRule('name','text:min=2|required');
        }
        if ($settigns['phone']['required'] == true) {
            $data->addRule('phone','text:min=2|required');
        }
        if ($settigns['email']['required'] == true) {
            $data->addRule('email','email:|required');
        }
        if ($settigns['username']['required'] == true) {
            $data->addRule('username','text:min=2|required');
        }
        $data->validate();

        return $this->getResponse();   
    }

    public function verifyEmail($request, $response, $data)
    {
        $this->onDataValid(function($data) { 
        });
       
        return $this->getResponse();   
    }

    public function changeDetails($request, $response, $data) 
    { 
        // get current auth user
        $user = Arikaim::auth()->getUser();

        $this->onDataValid(function($data) use($user)  { 
            // save user 
            $result = $user->update($data->toArray());
            // saev user details
            $result = Model::UserDetails('Users')->saveDetails($user->id,$data->toArray());

            $this->setResult(['message' => 'Chnages saved','data' => $data->toArray()]);           
        });
        
        $data
            ->addRule('uuid','exists:model=Users|field=uuid')
            ->addRule('first_name','text:min=2')
            ->addRule('user_name','unique:model=Users|field=user_name|required|exclude=' . $user->user_name,'Username exist')
            ->addRule('email','unique:model=Users|field=email|exclude=' . $user->email,'Email exist')
            ->validate();

        return $this->getResponse();   
    }

    public function read($request, $response, $data)
    {
        $user = Arikaim::auth()->getUser();

        if (is_object($user) == true) {
            $this->setResult($user->toArray());
        } else {
            $this->setError('Not loged in');
        }
       
        return $this->getResponse();  
    }

    /**
     * Logout
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function login($request, $response, $data)
    {             
        if (Arikaim::auth()->getLoginAttempts() > 1) {
            $this->verifyCaptcha('users.login.captcha.protect',$request,$data);           
        }
      
        $this->onDataValid(function($data) { 
            $login_with = Arikaim::options()->get('users.login.with'); 
            $credentials['password'] = $data->get('password');

            if ($login_with == 1 || $login_with == 3) {
                $credentials['user_name'] = $data->get('user_name');
            }

            if ($login_with == 2 || $login_with == 3) {
                $credentials['email'] = $data->get('email');
            }           
            $result = Arikaim::auth()->authenticate($credentials);
      
            if ($result === false) {   
                $this->setResult(['attempts' => Arikaim::auth()->getLoginAttempts()]);        
                $this->setError(Arikaim::getError("LOGIN_ERROR"));   
            } else {        
                $remember = $data->get('remember',false);
                if ($remember == true) {
                    // TODO
                }
                $token = Arikaim::auth()->provider('jwt')->createToken(Arikaim::auth()->getId());         
                $this->setResult(['message' => 'Done','token' => $token, 'redirect_url' => '/user']);
            }    
        });

        $data
            ->addRule('user_name',"text:min=2")
            ->addRule('password',"text:min=2")
            ->validate();
    
        return $this->getResponse();
    }

    /**
     * Reset password
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function resetPassword($request, $response, $data)
    {
        $messages = HtmlComponent::getProperties('users::reset-password');
        
        $this->onDataValid(function($data) use($messages) {
            $model = Model::Users()->findByColumn($data->get('email'),'email');
            $properties = [
                'user'                => $model->toArray(),
                'reset_password_url'  => $this->createPasswordResetUrl($model->id)
            ];
            $result = Mail::create()
                ->loadComponent('users::emails.reset-password',$properties)
                ->to($model->email)
                ->send();

            if ($result === false) {
                $this->setError($messages->getByPath('messages/errors/send'));
                $this->setError(Arikaim::mailer()->getErrorMessage());
            } else {
                $this->setResult(['message' => 'Email with password reset link send.']);
            }
        });

        $data
            ->addRule('email',"exists:model=Users|field=email|required",$messages->getByPath('messages/errors/email'))
            ->validate();

        return $this->getResponse(); 
    }

    /**
     * Logout
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function logout($request, $response, $data)
    {
        Arikaim::auth()->logout();  
        return $this->getResponse();
    }

     /**
     * Change password
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function changePassword($request, $response, $data)
    {
        $this->onDataValid(function($data) { 
            $password = $data->get('password');
            $user = Arikaim::auth()->getUser(); 
            
            if (is_object($user) == false) {
                return $this
                    ->setError('Not valid or expired access token')
                    ->getResponse();
            }

            $result = $user->changePassword($user->id,$password);
            if ($result !== false) {
                $this->setResult(['message' => 'Password changed.']);
            } else {
                $this->setError('Error change password');
            }
        });

        $repeat_password = $data->get('repeat_password');
        $data
            ->addRule('uuid','exists:model=Users|field=uuid')
            ->addRule('repeat_password','text:min=4|required')
            ->addRule('password','text:min=4|required')
            ->addRule('password','equal:value=' . "$repeat_password|required",'Password and repeat password does not match.')
            ->validate();
        
        return $this->getResponse();
    }

    public function verifyCaptcha($option_name, $request, $data)
    {
        $captcha_protect = Arikaim::options()->get($option_name);
        if ($captcha_protect == true) {
            $current = Arikaim::options()->get('captcha.current');
            $recaptcha = Arikaim::driver()->create($current . ':captcha');
          
            $result = $recaptcha->verify($data['g-recaptcha-response'],$request->getAttribute('client_ip'));
            if ($result == false) {
                $this->setError('Captcha verification failed.');
                return false;
            } 
        }
        return true;
    }

    public function createPasswordResetUrl($user_id)
    {
        $access_token = Arikaim::auth()->provider('token')->createToken($user_id);       
        return (is_object($access_token) == true) ? Url::ARIKAIM_BASE_URL . '/change-password/' . $access_token->token : null;
    }
}
