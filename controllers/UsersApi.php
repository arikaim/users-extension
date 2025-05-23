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
    * Send confirm email
    * 
    * @param \Psr\Http\Message\ServerRequestInterface $request
    * @param \Psr\Http\Message\ResponseInterface $response
    * @param \Arikaim\Core\Validator\Validator $data
    * @return Psr\Http\Message\ResponseInterface
    */
    public function sendConfirmEmail($request, $response, $data) 
    {   
        $result = $this->get('access')->withProvider('token')->authenticate($data->toArray());
        if ($result === false) {
            $this->error('errors.token','Access token not valid.');
            return; 
        }

        $user = $this->get('access')->getUser();                 
        if ($user === null) {
            $this->error('errors.token','Access token not valid.');
            return;               
        }

        $result = $this->sendConfirmationEmail($user);

        if ($result === false) {
            $this->error('errors.confirm.email','Error send confirm email.');
            return false;
        }

        $this
            ->message('confirm.email','Confirm email send')
            ->field('uuid',$user['uuid'])
            ->field('email',$user['email']);
    }

    /**
     * Signup api
     *
     * @Api(
     *      description="User signup",    
     *      parameters={
     *          @ApiParameter (name="username",type="string",description="User name"),
     *          @ApiParameter (name="email",type="string",description="User email address"),
     *          @ApiParameter (name="repeat_password",type="string",description="Repeat password field"),
     *          @ApiParameter (name="password",type="string",required=true,description="User password")
     *      }
     * )
     * 
     * @ApiResponse(
     *      fields={
     *          @ApiParameter (name="uuid",type="string",description="User uuid"),
     *          @ApiParameter (name="redirect_url",type="string",description="Redirect url"),
     *          @ApiParameter (name="email_send",type="boolean",description="True if email is send to user"),
     *          @ApiParameter (name="status",type="integer",description="User status"),
     *      }
     * )   
     * 
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param \Arikaim\Core\Validator\Validator $data
     * @return mixed
    */
    public function signup($request, $response, $data) 
    {       
        $settings = $this->get('options')->get('users.signup.form');
        $captchaProtect = $settings['captcha']['show'] ?? false;
        if ($captchaProtect == true) {                  
            if ($this->verifyCaptcha($request,$data) == false) {               
                return false;
            } 
        }
       
        $repeatPassword = $data->get('repeat_password');
        $data                 
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
            $data           
                ->addRule('regexp:exp=/^[A-Za-z][A-Za-z0-9]{2,32}$/|required','user_name',$this->getMessage('errors.username.valid')) 
                ->addRule('text:min=2|required','user_name');
            // 
            if ($this->get('service')->has('content.moderation') == true) {
                // check user name
                $result = $this->get('service')->get('content.moderation')->containWord($data['user_name'] ?? '','user.signup');
                if ($result == true) {                                      
                    $this->error('errors.username.invalid','Not valid user name.');
                    return false;
                }
            }
        }           

        $data
            ->addFilter('email','Trim')    
            ->addFilter('user_name','Trim')  
            ->validate(true);    

        $user = $this->userSignup($data,$settings);
        $redirectUrl = $data->get('redirect_url','');
        $group = $data->get('group',null);

        if ($user === false) {
            $this->error('errors.signup','Error user signup');
            return false;
        }

        // send confirm email to user
        $sendConfirmEmail = (bool)$this->get('options')->get('users.notifications.email.verification',false);
        $emailSend = ($sendConfirmEmail === true) ? $this->sendConfirmationEmail($user->toArray()) : false;
    
        if (empty($redirectUrl) == false) {
            $redirectUrl = Text::render($redirectUrl,['user' => $user->uuid]);
            $redirectUrl = (Url::isRelative($redirectUrl) == true) ? Page::getUrl($redirectUrl,true) : $redirectUrl;
        }
        
        // add user to gorup
        Model::UserGroups()->addUser($group,$user->id);
        
        $this
            ->message('signup')
            ->field('uuid',$user->uuid)
            ->field('user_group',$group)
            ->field('redirect_url',$redirectUrl)
            ->field('email_send',$emailSend)
            ->field('status',$user->status);                                                       
    }

    /**
     * Change user details page
     * 
     * @Api(
     *      description="Change user details",    
     *      parameters={
     *          @ApiParameter (name="first_name",type="string",description="User first name"),
     *          @ApiParameter (name="last_name",type="string",description="User last name"),
     *          @ApiParameter (name="user_name",type="string",description="Username field"),
     *          @ApiParameter (name="email",type="string",required=true,description="User email")
     *      }
     * )
     *
     * @ApiResponse(
     *      fields={
     *          @ApiParameter (name="uuid",type="string",description="User uuid")
     *      }
     * )  
     * 
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return mixed
    */
    public function changeDetails($request, $response, $data) 
    { 
        // get current auth user
        $user = $this->get('access')->getUser();
        $data          
            ->addRule('text:min=2','first_name')
            ->addRule('htmlTags','first_name',$this->getMessage('errors.html'))
            ->addRule('htmlTags','last_name',$this->getMessage('errors.html'))
            ->addRule('unique:model=Users|field=user_name|exclude=' . $user['user_name'],'user_name',$this->getMessage('errors.username.exist'))
            ->addRule('unique:model=Users|field=email|exclude=' . $user['email'],'email',$this->getMessage('errors.email'))
            ->validate(true);    

        $userName = $data->getString('user_name',null);
        $userModel = Model::Users()->findById($user['id']);
        // save user 
        $details = [           
            'email' => $data->getString('email',null)
        ];
        if (empty($userName) == false) {
            $details['user_name'] = $userName;
        }

        $result = $userModel->update($details);
        if ($result == false) {
            $this->error('errors.update');
            return false;
        }

        // ceate user details
        Model::UserDetails('Users')->findOrCreate($user['id']);

        // save user details
        $result = Model::UserDetails('Users')->saveDetails($user['id'],$data->toArray());
        if ($result === false) {
            $this->error('errors.update');
            return false;
        }

        $this
            ->message('update')
            ->field('uuid',$user['uuid']);            
    }

    /**
     * User Login
     *
     * @Api(
     *      description="User login, depend of users extension settings user_name field or email is required.",    
     *      parameters={
     *          @ApiParameter (name="user_name",type="string",description="User name"),
     *          @ApiParameter (name="email",type="string",description="User email address"),
     *          @ApiParameter (name="password",type="string",required=true,description="User password")
     *      }
     * )
     * 
     * @ApiResponse(
     *      fields={
     *          @ApiParameter (name="uuid",type="string",description="User uuid")
     *      }
     * )         
     * 
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return mixed
    */
    public function login($request, $response, $data)
    {       
        $loginWith = $this->get('options')->get('users.login.with',3);
      
        // user name
        if ($loginWith == 1 || $loginWith == 3) {
            $data->addRule('text:min=2|required','user_name');
        }
        // email
        if ($loginWith == 2) {
            $data->addRule('email:|required','email');
        }
        $data
            ->addRule('text:min=2|required','password')
            ->addFilter('email','Trim')    
            ->addFilter('user_name','Trim')           
            ->validate(true);    

        $loginAttempts = $this->get('access')->getLoginAttempts();

        if ($loginAttempts > 1) {
            $captchaProtect = $this->get('options')->get('users.login.captcha.protect');
            if ($captchaProtect == true) {      
                if ($this->verifyCaptcha($request,$data) == false) {                                            
                    return false;
                }                  
            }
        }
    
        $remember = $data->get('remember',false);
        $credentials = $this->resolveLoginCredentials($loginWith,$data);

        $this->userLogin($credentials,$remember,'session',$loginAttempts);
    }

    /**
     * Reset password
     *
     * @Api(
     *      description="Generate protected url for user password change and email url to user",    
     *      parameters={
     *          @ApiParameter (name="email",type="string",description="User email",required=true)         
     *      }
     * )
     * 
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function resetPasswordController($request, $response, $data)
    {  
        $data
            ->addRule('exists:model=Users|field=email|required','email',$this->getMessage('errors.not-valid'))
            ->validate(true);       
       
        $user = Model::Users()->findByColumn($data->get('email'),'email');
        if ($user == null) {
            $this->error('errors.email.notvalid','Not valid email.');
            return;
        }
        $properties = [
            'user'               => $user->toArray(),
            'domain'             => \constant('DOMAIN'),
            'reset_password_url' => $this->createProtectedUrl($user->id,'change-password')
        ];

        $result = $this->get('mailer')->create('users>reset-password',$properties)                
            ->to($user->email)
            ->send();

        if ($result === false) {
            $this->error('errors.reset-password','Error send password reset link.');
            return;
        }
                          
        $this
            ->message('reset.password.email')
            ->field('email',$data->get('email'));                                 
    }

    /**
     * Logout
     *
     * @Api(
     *      description="User logout"        
     * )
     * 
     * @ApiResponse(
     *      fields={
     *          @ApiParameter (name="redirect_url",type="string",description="Redirect url")
     *      }
     * )  
     * 
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function logout($request, $response, $data)
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
     * @Api(
     *      description="Change user password",    
     *      parameters={
     *          @ApiParameter (name="password",type="string",description="New pasword",required=true),   
     *          @ApiParameter (name="repeat_password",type="string",description="Repeat pasword",required=true)         
     *      }
     * )
     * 
     * @ApiResponse(
     *      fields={
     *          @ApiParameter (name="uuid",type="string",description="User uuid")
     *      }
     * )  
     * 
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function changePassword($request, $response, $data)
    {      
        $repeatPassword = $data->get('repeat_password');
        $data        
            ->addRule('exists:model=Users|field=uuid','uuid')
            ->addRule('text:min=4|required','repeat_password')
            ->addRule('text:min=4|required','password')
            ->addRule('equal:value=' . $repeatPassword . '|required','password','Password and repeat password does not match.')
            ->validate(true);       

        $password = $data->get('password');
        $user = $this->get('access')->getUser(); 
                
        if (\is_array($user) === false) {
            $this->error('errors.token','Access token not valid.');
            return;               
        }
    
        $result = Model::Users()->changePassword($user['id'],$password);   

        $this->setResponse($result,function() use($user) {                         
            $this                    
                ->message('password')                  
                ->field('uuid',$user['uuid']);                                     
        },'errors.password');                      
    }

    /**
     * Resolve login credentials
     *
     * @param integer $loginWith
     * @param Collection $data
     * @return array
     */
    protected function resolveLoginCredentials(int $loginWith, $data): array
    {
        $credentials['password'] = \trim($data->get('password',''));
        $userName = \strtolower(\trim($data->get('user_name','')));

        switch($loginWith) {
            case 1: 
                $credentials['user_name'] = $userName;    
                break;
            case 2: 
                $credentials['email'] = $data->get('email');  
                break;
            case 3: 
                if (Utils::isEmail($data->get('user_name')) == true) {
                    $credentials['email'] = $data->get('user_name');
                } else {
                    $credentials['user_name'] = $userName;  
                }
                break;

            default:
                $credentials['user_name'] = $userName;
        }

        return $credentials;
    }   

    /**
     * Logout
     *
     * @Api(
     *      description="Get user details"        
     * )
     * 
     * @ApiResponse(
     *      fields={
     *          @ApiParameter (name="redirect_url",type="string",description="Redirect url")
     *      }
     * )  
     * 
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface|bool
    */
    public function getDetails($request, $response, $data)
    {
        $userId = $this->getUserId(); 
        $userDetails = Model::Users()->findById($userId);

        if ($userDetails == null) {
            $this->error('Access denied');
            return false;
        }

        $this
            ->field('email',$userDetails->email)
            ->field('username',$userDetails->username)
            ->field('avatar',$userDetails->details->avatar);       
    }
}
