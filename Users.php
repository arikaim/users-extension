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
        $this->addPageRoute('/logout',"Users",'logout','users>user.logout',null,'logout.page'); 
        $this->addPageRoute('/signup',"Users",'signup','users>user.signup',null,'signup.page'); 
        $this->addPageRoute('/login',"Users",'login','users>user.login',null,'login.page');  
        $this->addPageRoute('/change-password/{token}','Users','changePassword','users>user.change-password','token');  
        $this->addPageRoute('/email/confirm/{token}','Users','emailConfirm','users>user.email-confirm','token');            
        $this->addPageRoute('/user[/{menu}]','Users','userArea','users>user','session');
        // set auth error redirect url
        $this->setRouteRedirectUrl('GET','/user[/{menu}]','/login');
        $this->addPageRoute('/user/profile/{uuid}','Users','userProfile','users>user.profile');
        
        // Api 
        $this->addApiRoute('POST','/api/users/login','UsersApi','login');  
        $this->addApiRoute('POST','/api/users/signup','UsersApi','signup');  
        $this->addApiRoute('PUT','/api/users/reset-password','UsersApi','resetPassword'); 
        $this->addApiRoute('GET','/api/users/logout','UsersApi','logout');  
        $this->addApiRoute('PUT','/api/users/change-password','UsersApi','changePassword','token');  
        // user admin panel
        $this->addApiRoute('PUT','/api/users/update','UsersApi','changeDetails','session');   
        $this->addApiRoute('PUT','/api/users/profile/change-password','UsersApi','changePassword','session');      
        // avatar
        $this->addApiRoute('GET','/api/users/avatar/view','UsersAvatarApi','viewUserAvatar','session'); 
        $this->addApiRoute('POST','/api/users/avatar/upload','UsersAvatarApi','uploadAvatar','session'); 
        $this->addApiRoute('DELETE','/api/users/avatar/delete','UsersAvatarApi','deleteAvatar','session'); 
        // for public profile
        $this->addApiRoute('GET','/users/avatar/view/{uuid}','UsersAvatarApi','viewAvatar'); 

        // Control Panel Api
        $this->addApiRoute('POST','/api/users/admin/add','UsersControlPanel','add','session'); 
        $this->addApiRoute('PUT','/api/users/admin/update','UsersControlPanel','update','session'); 
        $this->addApiRoute('PUT','/api/users/admin/change-password','UsersControlPanel','changePassword','session'); 
        $this->addApiRoute('PUT','/api/users/admin/status','UsersControlPanel','setStatus','session'); 
        $this->addApiRoute('DELETE','/api/users/admin/delete/{uuid}','UsersControlPanel','softDelete','session'); 
        $this->addApiRoute('GET','/api/users/admin/list/{data_field}/[{query}]','UsersControlPanel','getList','session');  
        // avatar
        $this->addApiRoute('POST','/api/users/admin/avatar/upload','UsersControlPanel','uploadAvatar','session'); 
        $this->addApiRoute('DELETE','/api/users/admin/avatar/delete/{uuid}','UsersControlPanel','deleteAvatar','session'); 
        $this->addApiRoute('GET','/api/users/admin/avatar/view/{uuid}','UsersControlPanel','viewAvatar','session'); 

        // Restore soft deleted user
        $this->addApiRoute('PUT','/api/users/admin/restore','UsersControlPanel','restore','session');  
        // Emty trash
        $this->addApiRoute('DELETE','/api/users/admin/trash/empty','UsersControlPanel','emptyTrash','session');  
        // User groups
        $this->addApiRoute('POST','/api/users/admin/groups/add','GroupsControlPanel','add','session'); 
        $this->addApiRoute('PUT','/api/users/admin/groups/update','GroupsControlPanel','update','session'); 
        $this->addApiRoute('DELETE','/api/users/admin/groups/delete/{uuid}','GroupsControlPanel','delete','session');  
        // group members
        $this->addApiRoute('PUT','/api/users/admin/groups/add/member','GroupsControlPanel','addMember','session'); 
        $this->addApiRoute('PUT','/api/users/admin/groups/remove/member','GroupsControlPanel','removeMember','session'); 

        // Permissions
        $this->addApiRoute('POST','/api/users/admin/permission/add','PermissionsControlPanel','addPermission','session');  
        $this->addApiRoute('PUT','/api/users/admin/permission/update','PermissionsControlPanel','updatePermission','session');  
        $this->addApiRoute('DELETE','/api/users/admin/permission/delete/{uuid}','PermissionsControlPanel','deletePermission','session');   
        $this->addApiRoute('PUT','/api/users/admin/permission/grant','PermissionsControlPanel','grantPermission','session');   
        $this->addApiRoute('PUT','/api/users/admin/permission/deny','PermissionsControlPanel','denyPermission','session');   
        
        // Create db tables
        $this->createDbTable('UserTypeSchema');
        $this->createDbTable('UserDetailsSchema');     
        $this->createDbTable('UserOptionTypeSchema');  
        $this->createDbTable('UserOptionsSchema');  
        $this->createDbTable('UserOptionsListSchema');  
        
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
        $this->createOption('users.default.type',null);
        // sign in with 
        $this->createOption('users.sign.with.github',false);
        $this->createOption('users.sign.with.facebook',false);
        $this->createOption('users.sign.with.google',false);
        $this->createOption('users.sign.with.twitter',false);

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
