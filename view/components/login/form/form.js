/**
 *  Arikaim
 *  
 *  @copyright  Copyright (c) Konstantin Atanasov <info@arikaim.com>
 *  @license    http://www.arikaim.com/license
 *  http://www.arikaim.com
 * 
 *  Extension: Users
 */

$(document).ready(function() {
    arikaim.ui.viewPasswordButton('.view-password','#password');

    arikaim.ui.form.addRules("#login_form",{
        inline: false,
        fields: {
            password: {
                rules: [{ type: "minLength[4]" }]
            },
            user_name: {
                rules: [{ type: "empty" }]
            }
        }
    });

    arikaim.ui.form.onSubmit('#login_form',function() {
        return users.login();
    },function(result) {   
        if (isEmpty(result.redirect_url) == false) {
            arikaim.page.load(result.redirect_url);
        }      
    },function(errors) {
        if (users.getLoginAttempts() > 0) {          
            arikaim.page.loadContent({
                id : 'captcha_panel',
                component: 'captcha::code'
            });
        }
    });
});