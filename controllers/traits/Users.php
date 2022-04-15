<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Extensions\Users\Controllers\Traits;

use Arikaim\Core\Db\Model;
use Arikaim\Core\Validator\Validator;
use Arikaim\Core\Http\Cookie;
use Arikaim\Core\Http\Url;

/**
 * Users trait
*/
trait Users 
{
    /**
     * Login user
     *
     * @param array $credentials
     * @param boolean $remember
     * @param string $authProviderName
     * @return void
     */
    public function userLogin(array $credentials, bool $remember = false, string $authProviderName = 'session'): void
    {
        $result = $this->get('access')->withProvider($authProviderName)->authenticate($credentials);
           
        $this->setResponse($result,function() use ($remember) {  
            $user = $this->get('access')->getUser();  
            Model::Users()->findById($user['uuid'])->updateLoginDate();
            
            if ($remember == true) {
                // remember user login                                
                @Cookie::add('user',$user['uuid']);
                $accessToken = Model::AccessTokens()->createToken($user['id'],1,4800);                      
                @Cookie::add('token',$accessToken['token']);                                  
            } else {       
                // remove token
                Cookie::delete('user');
                Cookie::delete('token');                  
            }
        
            $jwtToken = $this->get('access')->createProvider('jwt')->createToken($user['auth_id']);
            // dispatch event
            $this->get('event')->dispatch('user.login',$user);
            $this
                ->message('login')
                ->field('uuid',$user['uuid'])
                ->field('token',$jwtToken)
                ->field('redirect_url',$this->getLoginRedirectUrl());                           
        },function() use ($loginAttempts) {    
            $this
                ->error('errors.login')     
                ->field('attempts',$loginAttempts);                                           
        }); 
    }

    /**
     * Get login redirect url
     *
     * @return string|null
     */
    public function getLoginRedirectUrl(): ?string
    {
        $redirectUrl = $this->get('options')->get('users.login.redirect',null); 
        return (empty($redirectUrl) == false) ? Url::BASE_URL . '/' . $redirectUrl : Url::BASE_URL;    
    }

    /**
     * User signup
     *
     * @param Validator $data
     * @param array $settings
     * @return object|bool
     */
    public function userSignup(Validator $data, array $settings)
    {       
        $activation = (int)$this->get('options')->get('users.sugnup.activation',1);
        
        $model = Model::Users();
        $userName = $data->getString('user_name',null);
        $email = $data->getString('email',null);
        $password = $data->getString('password',null);
        $options = $data->get('options',null);

        // user type
        $data['type_id'] = $this->getUserTypeId($data->get('user_type_slug',null));
        
        // verify username
        if ($settings['username']['required'] == true) {
            if ($model->hasUserName($userName) == true) {              
                $this->error('errors.username.exist');
                return false;
            }
        }
        // verify email
        if ($settings['email']['required'] == true) {
            if ($model->hasUserEmail($email) == true) {               
                $this->error('errors.email');
                return false;
            }
        }
      
        $user = $model->createUser($userName,$password,$email);
       
        if (\is_object($user) == false) {          
            $this->error('errors.signup');
            return false;
        } 
        if ($activation == 2) {
            // set user PENDING status
            $user->setStatus(4);
        }

        $userDetails = Model::UserDetails('users');
        $userDetails->saveDetails($user->id,$data->toArray());
        // create options
        $userDetails = $userDetails->findOrCreate($user->id);
        $userDetails->createOptions();  
      
        // dispatch event   
        $params = $user->toArray();
        $params['options'] = $options;
        $this->get('event')->dispatch('user.signup',$params); 
        
        return $user;
    }

    /**
     * Get user type Id
     *
     * @param string|integer|null $typeSlug
     * @return int|null
     */
    public function getUserTypeId($typeSlug = null): ?int
    {
        if (empty($typeSlug) == true) {
            return null;
        }
        if (\is_numeric($typeSlug) == true) {
            return $typeSlug;
        }
        $userType = Model::create('UserType','users')->findBySlug($typeSlug);
        
        return (\is_object($userType) == true) ? $userType->id : null;
    }

     /**
     * Send confirm email to user
     *
     * @param array $user
     * @return boolean
     */
    public function sendConfirmationEmail(array $user)
    {
        $properties = [
            'user'              => $user,
            'domain'            => DOMAIN,
            'confirm_email_url' => $this->createProtectedUrl($user['id'],'email/confirm')
        ];

        try {
            $result = $this->get('mailer')->create('users>confirmation',$properties)           
                ->to($user['email'])
                ->send();   
        } catch (\Exception $e) {
            return false;
        }
        
        return $result;
    }    
}
