/**
 *  Arikaim
 *  
 *  @copyright  Copyright (c) Konstantin Atanasov <info@arikaim.com>
 *  @license    http://www.arikaim.com/license
 *  http://www.arikaim.com
 *  
 *  Extension: Users
 *  Component: users::signup.form
 */

$(document).ready(function() { 
    arikaim.ui.viewPasswordButton('.view-password');
    
    var rules = {
        inline: false,
        fields: {
            user_name: {
                rules: [{ type:'minLength[2]' }]
            },
            email: {
                rules: [{ type: 'email' }]
            },
            password: {
                rules: [{ type: "minLength[4]" }]
            },
            repeat_password: {
                rules: [{ type: "minLength[4]" }]
            }
        }
    };

    arikaim.ui.form.addRules("#signup_form",rules);

    arikaim.ui.form.onSubmit('#signup_form',function() {
        return users.signup('#signup_form');
    },function(result) {     
        console.log(result);
    },function(errors) {
        console.log(errors);
    });
});