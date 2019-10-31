/**
 *  Arikaim
 *  
 *  @copyright  Copyright (c) Konstantin Atanasov <info@arikaim.com>
 *  @license    http://www.arikaim.com/license
 *  http://www.arikaim.com
 * 
 */

function Users() {

    var loginAttempts = 0;

    this.changePassword = function(options,onSuccess,onError) {
        var form_id = getValue('formId',options,'#change_password_form');
        return arikaim.put('/api/users/change-password/',form_id,onSuccess,onError);
    };

    this.resetPassword = function(options,onSuccess,onError) {
        var form_id = getValue('formId',options,'#reset_password_form');
        return arikaim.put('/api/users/reset-password/',form_id,onSuccess,onError);
    };
   
    this.signup = function(options,onSuccess,onError) {   
        var form_id = getValue('formId',options,'#signup_form');
        return arikaim.post('/api/users/signup/',form_id,onSuccess,onError);
    };

    this.changeDetails = function(options,onSuccess,onError) {
        var form_id = getValue('formId',options,'#details_form');
        return arikaim.put('/api/users/edit/',form_id,onSuccess,onError);
    };

    this.login = function(options,onSuccess,onError) {
        var form_id = getValue('formId',options,'#login_form');
        return arikaim.post('/api/users/login/',form_id,onSuccess,function(errors) {
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
