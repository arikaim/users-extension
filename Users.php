<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2016-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Extensions\Users;

use Arikaim\Core\Packages\Extension\Extension;
use Arikaim\Core\Arikaim;

/**
 * Users extension
*/
class Users extends Extension
{
    /**
     * Install extension routes, events, jobs
     *
     * @return boolean
    */
    public function install()
    {
        // Pages
        $this->addPageRoute('/login','login');  
        $this->addPageRoute('/signup','signup');  
        $this->addPageRoute('/change-password/{token}','change-password','token');  
        $this->addPageRoute('/user','user-area','session');

        $this->addAuthErrorRoute('/error',null,'session','/login');

        // Api 
        $result = $this->addApiRoute('POST','/api/users/login/','Users','login');  
        $result = $this->addApiRoute('POST','/api/users/signup/','Users','signup');  
        $result = $this->addApiRoute('PUT','/api/users/reset-password/','Users','resetPassword');  
        $result = $this->addApiRoute('PUT','/api/users/change-password/','Users','changePassword','token');  
        $result = $this->addApiRoute('PUT','/api/users/edit/','Users','changeDetails','session');       
        $result = $this->addApiRoute('GET','/api/users/logout','Users','logout');  
        // Control Panel Api
        $result = $this->addApiRoute('POST','/api/users/admin/add','UsersControlPanel','add','session'); 
        $result = $this->addApiRoute('PUT','/api/users/admin/update','UsersControlPanel','update','session'); 
        $result = $this->addApiRoute('PUT','/api/users/admin/change-password','UsersControlPanel','changePassword','session'); 
        $result = $this->addApiRoute('PUT','/api/users/admin/status','UsersControlPanel','setStatus','session'); 
        $result = $this->addApiRoute('DELETE','/api/users/admin/{uuid}','UsersControlPanel','delete','session'); 
        // Create db tables
        $this->createDbTable('UserDetailsSchema');
        // Events
        $this->registerEvent('users.login','Trigger after user login');
        $this->registerEvent('users.logout','Trigger after user logout');
        $this->registerEvent('users.signup','Trigger after user signup');
        $this->registerEvent('users.delete','Trigger after user deleted');
        $this->registerEvent('users.edit','Trigger after user edited');
        // Options
        $this->createOption('users.login.with',1);
        $this->createOption('users.login.captcha.protect',true);
        $this->createOption('users.sugnup.activation',1);
        $this->createOption('users.notifications.email.signup',true);
        $this->createOption('users.notifications.email.welcome',true);
        $this->createOption('users.notifications.email.verification',false);

        $signup_settings = [
            'name'       => ['show' => false,'required' => false],
            'email'      => ['show' => true,'required'  => true],
            'username'   => ['show' => false,'required' => false],         
            'phone'      => ['show' => false,'required' => false],
            'captcha'    => ['show' => true,'required'  => true]                    
        ];
        $this->createOption('users.signup.form',$signup_settings);

        return true;
    }   
}
