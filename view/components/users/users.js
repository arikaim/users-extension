/**
 *  Arikaim  
 *  @copyright  Copyright (c) Konstantin Atanasov <info@arikaim.com>
 *  @license    http://www.arikaim.com/license
 *  http://www.arikaim.com
 */

function Users() {
    this.self = this;
    this.onLogin = null;

    var loginAttempts = 0;
    
    this.onSignUp = function(result) {
        arikaim.page.loadContent({
            id: 'signup_content',
            component: 'users>signup.message',
            params: { uuid: result.uuid }
        });  
    };

    this.changePassword = function(formId,onSuccess,onError) {
        var formId = getDefaultValue(formId,'#change_password_form');

        return arikaim.put('/api/users/change-password',formId,onSuccess,onError);
    };

    this.resetPassword = function(formId,onSuccess,onError) {
        var formId = getDefaultValue(formId,'#reset_password_form'); 

        return arikaim.put('/api/users/reset-password',formId,onSuccess,onError);
    };
   
    this.signup = function(formId,onSuccess,onError) {   
        var formId = getDefaultValue(formId,'#signup_form'); 
      
        return arikaim.post('/api/users/signup',formId,onSuccess,onError);
    };

    this.changeDetails = function(formId,onSuccess,onError) {
        var formId = getDefaultValue(formId,'#details_form'); 
     
        return arikaim.put('/api/users/update',formId,onSuccess,onError);
    };

    this.login = function(formId,onSuccess,onError) {
        var formId = getDefaultValue(formId,'#login_form'); 
       
        return arikaim.post('/api/users/login',formId,onSuccess,function(errors) {
            loginAttempts++;
            callFunction(onError,errors);
        });
    };

    this.logout = function(onSuccess,onError) {
        loginAttempts = 0;
        return arikaim.get('/api/users/logout',onSuccess,onError);
    };

    this.getLoginAttempts = function() {
        return loginAttempts;
    };
}

var users = new Users();
