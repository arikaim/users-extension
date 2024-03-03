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

use Arikaim\Core\Controllers\Controller;
use Arikaim\Core\Db\Model;
use Arikaim\Core\Http\Cookie;
use Arikaim\Core\Http\Session;
use Arikaim\Core\Http\Url;
use Exception;

/**
 * Users pages controler
*/
class Users extends Controller
{
    /**
     * User area page
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function userArea($request, $response, $data) 
    { 
        $language = $this->getPageLanguage($data);
    
        if ($this->get('access')->isLogged() == false) {         
            return $this->withRedirect($response,'/login');              
        }
        
        // get current auth user
        $data['user'] = $this->get('access')->getUser();            
        $response = $this->noCacheHeaders($response);

        return $this->pageLoad($request,$response,$data,'users>user',$language);
    }

    /**
     * User signup page
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function signupPage($request, $response, $data) 
    {        
        // remove token
        Cookie::delete('user');
        Cookie::delete('token');    

        $this->get('access')->logout();            
    }

    /**
     * User logout page
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

        return $this->withRedirect($response,$redirectUrl);  
    }

    /**
     * User profile page
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function userProfilePage($request, $response, $data) 
    { 
        $uuid = $data->get('uuid',null);
        $data['user'] = Model::Users()->findByid($uuid);
        if (\is_object($data['user']) == false) {           
            return $this->pageNotFound($response,$data->toArray());    
        }

        $data['details'] = Model::UserDetails('users')->findOrCreate($data['user']->id);      
        if (\is_object($data['details']) == false) {
            return $this->pageNotFound($response,$data->toArray());    
        }

        if ($data['details']->isPublic() == false) {          
            return $this->pageNotFound($response,$data->toArray());    
        }
    }
    
    /**
     * Change password page
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function changePasswordPage($request, $response, $data)
    {    
        $result = $this->get('access')->withProvider('token')->authenticate($data->toArray());
        if ($result == false) {
            return $this->pageNotFound($response,$data->toArray());   
        }
    }

    /**
     * Email confirm page
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function emailConfirmPage($request, $response, $data)
    {            
        $result = $this->get('access')->withProvider('token')->authenticate($data->toArray());
        if ($result == false) {
            return $this->pageNotFound($response,$data->toArray());   
        }

        $user = $this->get('access')->getUser();      

        if ($user == null) {
            return $this->pageNotFound($response,$data->toArray());    
        }

        $details = Model::UserDetails('users')->findByColumn($user['id'],'user_id');
        if ($details == null) {
            return $this->pageNotFound($response,$data->toArray());   
        }
        
        // set email confirmed
        $sendWelcomeEmail = (bool)$this->get('options')->get('users.notifications.email.welcome',false);
        $details->setEmailStatus(1);   

        if ($sendWelcomeEmail == true) {
            // send welcome email to user
            try {
                $this->get('mailer')
                    ->create('users>welcome',$user)
                    ->to($user['email'])
                    ->send();
            } catch (Exception $e) {               
            }
        }
    }

    /**
     * Login Page
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function login($request, $response, $data)
    {       
        $language = $this->getPageLanguage($data);
        $response = $this->noCacheHeaders($response);
       
        if ($this->get('access')->isLogged() == false) {
            return $this->pageLoad($request,$response,$data,'users>user.login',$language);
        }
        
        $redirectUrl = $this->get('options')->get('users.login.redirect','');
        $redirectUrl = (empty($redirectUrl) == false) ? Url::BASE_URL . '/' . $redirectUrl : Url::BASE_URL;  

        return $this->withRedirect($response,$redirectUrl);
    }
}
