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
        $this->addPageRoute('/logout','Users','logout','current>user.logout',null,'logout.page',true); 
        $this->addPageRoute('/signup[/{options}[/{language:[a-z]{2}}/]]','Users','signup','current>user.signup',null,'signup.page',false); 
        $this->addPageRoute('/login','Users','login','users>user.login',null,'login.page',true);  
        $this->addPageRoute('/change-password/{token}','Users','changePassword','current>user.change-password','token,public','change.password.page',true);  
        $this->addPageRoute('/email/confirm/{token}','Users','emailConfirm','current>user.email-confirm','token,public','email.confirm.page',true);            
        $this->addPageRoute('/user[/{menu}[/{language:[a-z]{2}}/]]','Users','userArea','current>user','session','user.area.page',false);
        // set auth error redirect url
        $this->setRouteRedirectUrl('GET','/user[/{menu}[/{language:[a-z]{2}}/]]','/login');
        $this->addPageRoute('/user/profile/{uuid}','Users','userProfile','users>user.profile',null,'user.profile.page',true);
        // Api 
        $this->addApiRoute('POST','/api/users/login','UsersApi','login');  
        $this->addApiRoute('POST','/api/users/signup','UsersApi','signup');  
        $this->addApiRoute('PUT','/api/users/reset-password','UsersApi','resetPassword'); 
        $this->addApiRoute('GET','/api/users/logout','UsersApi','logout',null);  
        $this->addApiRoute('PUT','/api/users/change-password','UsersApi','changePassword','token');  
        $this->addApiRoute('PUT','/api/users/confirm/email','UsersApi','sendConfirmEmail','token');  
        $this->addApiRoute('GET','/api/users/details','UsersApi','getDetails','token,session,jwt');  
        // user admin panel
        $this->addApiRoute('PUT','/api/users/update','UsersApi','changeDetails','session');   
        $this->addApiRoute('PUT','/api/users/profile/change-password','UsersApi','changePassword','session');      
        // avatar
        $this->addApiRoute('GET','/api/users/avatar/view/[{uuid}]','UsersAvatarApi','viewAvatar',null); 
        $this->addApiRoute('POST','/api/users/avatar/upload','UsersAvatarApi','uploadAvatar','session'); 
        $this->addApiRoute('DELETE','/api/users/avatar/delete','UsersAvatarApi','deleteAvatar','session');        
        // options
        $this->addApiRoute('PUT','/api/users/options','UsersOptionsApi','save',['session','jwt']); 
        $this->addApiRoute('PUT','/api/users/option/save','UsersOptionsApi','saveOption',['session','jwt']); 
        // tokens
        $this->addApiRoute('POST','/api/users/token/create','TokensApi','create','session');  
        $this->addApiRoute('PUT','/api/users/token/status','TokensApi','setStatus','session'); 
        $this->addApiRoute('PUT','/api/users/token/delete','TokensApi','delete','session'); 
        // Control Panel Api
        $this->addApiRoute('POST','/api/users/admin/add','UsersControlPanel','add','session'); 
        $this->addApiRoute('PUT','/api/users/admin/update','UsersControlPanel','update','session'); 
        $this->addApiRoute('PUT','/api/users/admin/change-password','UsersControlPanel','changePassword','session'); 
        $this->addApiRoute('PUT','/api/users/admin/status','UsersControlPanel','setStatus','session'); 
        $this->addApiRoute('DELETE','/api/users/admin/delete/{uuid}','UsersControlPanel','softDelete','session'); 
        $this->addApiRoute('GET','/api/users/admin/list/{data_field}/[{query}]','UsersControlPanel','getList','session');  
        // Restore soft deleted user
        $this->addApiRoute('PUT','/api/users/admin/restore','UsersControlPanel','restore','session');  
        // Emty trash
        $this->addApiRoute('DELETE','/api/users/admin/trash/empty','UsersControlPanel','emptyTrash','session');  
        // User groups
        $this->addApiRoute('POST','/api/users/admin/groups/add','GroupsControlPanel','add','session'); 
        $this->addApiRoute('PUT','/api/users/admin/groups/update','GroupsControlPanel','update','session'); 
        $this->addApiRoute('DELETE','/api/users/admin/groups/delete/{uuid}','GroupsControlPanel','delete','session');  
        // Group members
        $this->addApiRoute('PUT','/api/users/admin/groups/add/member','GroupsControlPanel','addMember','session'); 
        $this->addApiRoute('PUT','/api/users/admin/groups/remove/member','GroupsControlPanel','removeMember','session'); 
        // Permissions
        $this->addApiRoute('POST','/api/users/admin/permission/add','PermissionsControlPanel','addPermission','session');  
        $this->addApiRoute('PUT','/api/users/admin/permission/update','PermissionsControlPanel','updatePermission','session');  
        $this->addApiRoute('DELETE','/api/users/admin/permission/delete/{uuid}','PermissionsControlPanel','deletePermission','session');   
        $this->addApiRoute('PUT','/api/users/admin/permission/grant','PermissionsControlPanel','grantPermission','session');   
        $this->addApiRoute('PUT','/api/users/admin/permission/deny','PermissionsControlPanel','denyPermission','session');   
        $this->addApiRoute('PUT','/api/admin/users/permission/type','PermissionsControlPanel','updatePermissionType','session');
        // Create db tables
        $this->createDbTable('UserType');
        $this->createDbTable('UserDetails');     
        $this->createDbTable('UserOptionType');  
        $this->createDbTable('UserOptions');  
        // Events
        $this->registerEvent('user.login','Trigger after user login','UserEventDescriptor');
        $this->registerEvent('user.logout','Trigger after user logout');
        $this->registerEvent('user.signup','Trigger after user signup','UserEventDescriptor');       
        $this->registerEvent('user.before.delete','Trigger before delete user');       
        // Options       
        $this->createOption('users.login.with',3);
        $this->createOption('users.login.redirect','user');
        $this->createOption('users.signup.redirect','');
        $this->createOption('users.logout.redirect',null);
        $this->createOption('users.login.captcha.protect',true);
       
        $this->createOption('users.sugnup.activation',1);       
        $this->createOption('users.notifications.email.signup',false);
        $this->createOption('users.notifications.email.welcome',false);
        $this->createOption('users.notifications.email.verification',false);
        $this->createOption('users.default.type',null);
        // 
        $this->createOption('users.login.require.verified.email',false);

        // sign in with 
        $this->createOption('users.sign.with.github',false);
        $this->createOption('users.sign.with.facebook',false);
        $this->createOption('users.sign.with.google',false);
        $this->createOption('users.sign.with.twitter',false);

        $signupSettings = [
            'name'       => ['show' => false,'required' => false],
            'email'      => ['show' => true,'required'  => true],
            'username'   => ['show' => true,'required'  => true],         
            'phone'      => ['show' => false,'required' => false],
            'captcha'    => ['show' => true,'required'  => true]                    
        ];
        $this->createOption('users.signup.form',$signupSettings);
        // Reports
        $this->createReport();
        // Services
        $this->registerService('Users');
        // Register Job
        $this->registerJob('UserSignup');
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
