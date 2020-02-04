<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Extensions\Users;

use Arikaim\Core\Extension\Extension;
use Arikaim\Core\Arikaim;

/**
 * Users extension
*/
class Users extends Extension
{
    /**
     * Install extension routes, events, jobs
     *
     * @return void
    */
    public function install()
    {
        // Pages
        $this->addShowPageRoute('/login','users>login');  
        $this->addShowPageRoute('/signup','users>signup');  
        $this->addPageRoute('/change-password/{token}','Users','changePassword','users>change-password','token');            
        $this->addPageRoute('/user','Users','userArea','users>user','session');

        // Api 
        $this->addApiRoute('POST','/api/users/login','UsersApi','login');  
        $this->addApiRoute('POST','/api/users/signup','UsersApi','signup');  
        $this->addApiRoute('PUT','/api/users/reset-password','UsersApi','resetPassword');  
        $this->addApiRoute('PUT','/api/users/change-password','UsersApi','changePassword','token');  
        $this->addApiRoute('PUT','/api/users/update','UsersApi','changeDetails','session');       
        $this->addApiRoute('GET','/api/users/logout','UsersApi','logout');  
        // Control Panel Api
        $this->addApiRoute('POST','/api/users/admin/add','UsersControlPanel','add','session'); 
        $this->addApiRoute('PUT','/api/users/admin/update','UsersControlPanel','update','session'); 
        $this->addApiRoute('PUT','/api/users/admin/change-password','UsersControlPanel','changePassword','session'); 
        $this->addApiRoute('PUT','/api/users/admin/status','UsersControlPanel','setStatus','session'); 
        $this->addApiRoute('DELETE','/api/users/admin/delete/{uuid}','UsersControlPanel','softDelete','session'); 
        $this->addApiRoute('GET','/api/users/admin/list/[{query}]','UsersControlPanel','getList','session');  
        // Restore soft deleted user
        $this->addApiRoute('PUT','/api/users/admin/restore','UsersControlPanel','restore','session');  
        // Emty trash
        $this->addApiRoute('DELETE','/api/users/admin/trash/empty','UsersControlPanel','emptyTrash','session');  

        // Create db tables
        $this->createDbTable('UserDetailsSchema');
        // Events
        $this->registerEvent('user.login','Trigger after user login');
        $this->registerEvent('user.logout','Trigger after user logout');
        $this->registerEvent('user.signup','Trigger after user signup');       
        // Options       
        $this->createOption('users.login.with',1);
        $this->createOption('users.login.redirect','user');
        $this->createOption('users.logout.redirect',null);
        $this->createOption('users.login.captcha.protect',true);
        $this->createOption('users.sugnup.activation',1);       
        $this->createOption('users.notifications.email.signup',true);
        $this->createOption('users.notifications.email.welcome',true);
        $this->createOption('users.notifications.email.verification',false);
        
        // Relation map 
        $this->addRelationMap('user','Users');

        $signupSettings = [
            'name'       => ['show' => false,'required' => false],
            'email'      => ['show' => true,'required'  => true],
            'username'   => ['show' => false,'required' => false],         
            'phone'      => ['show' => false,'required' => false],
            'captcha'    => ['show' => true,'required'  => true]                    
        ];
        $this->createOption('users.signup.form',$signupSettings);
    }
    
    /**
     * Uninstall extension
     *
     * @return void
     */
    public function unInstall()
    {         
    }
}
