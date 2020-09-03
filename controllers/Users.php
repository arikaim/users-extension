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
use Arikaim\Core\Http\Url;
use Arikaim\Core\Http\Session;

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
    public function userAreaPage($request, $response, $data) 
    { 
        // get current auth user
        $data['user'] = $this->get('access')->getUser();
        if (empty($data['user']) == true) {
            $this->get('errors')->addError('ACCESS_DENIED');
            return false;            
        }                
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
        $this->get('access')->logout();   
        // remove token
        Cookie::delete('user');
        Cookie::delete('token');    

        $redirectUrl = $this->get('options')->get('users.logout.redirect',null); 
        $redirectUrl = (empty($redirectUrl) == false) ? Url::BASE_URL . '/' . $redirectUrl : Url::BASE_URL;  

        return $response->withHeader('Location',$redirectUrl);
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
            return false;
        }

        $data['details'] = Model::UserDetails('users')->findOrCreate($data['user']->id);      
        if (\is_object($data['details']) == false) {
            return false;
        }

        if ($data['details']->isPublic() == false) {          
            return false;
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
        $user = $this->get('access')->getUser();      
        $details = Model::UserDetails('users')->findByColumn($user['id'],'user_id');
        if (\is_object($details) == false) {
            return false;
        }

        $details->setEmailStatus(1);       
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
        $redirectPath = Url::BASE_URL . "/" . $this->get('options')->get('users.login.redirect');

        if ($this->get('access')->isLogged() == false) {
            $token = Cookie::get('token');
          
            if (empty($token) == false) {
                // try login with token    
                $tokenStorageProvider = Model::AccessTokens();   
                if ($tokenStorageProvider->getType($token) == 1) {
                    // login with token
                    $this->get('access')->withProvider('token',$tokenStorageProvider);
                    $result = $this->get('access')->authenticate(['token' => $token]);
                  
                    if ($result == true) {        
                        $authId = $this->get('access')->getUser()->getAuthId();
                        Session::set('auth.id',$authId);
                        Session::set('auth.login.time',time());
                        Session::remove('auth.login.attempts');      
                                       
                        return $response->withHeader('Location',$redirectPath);
                    }         
                }                                             
            }

            return $this->pageLoad($request, $response, $data,'users>user.login');
        }
    
        return $response->withHeader('Location',$redirectPath);
    }
}
