'use strict';

$(document).ready(function() {
    arikaim.ui.form.addRules("#create_user_form",{
        inline: false,
        fields: {
            user_name: {
                rules: [{ type: 'minLength[2]' }]
            },
            email: {
                rules: [{ type: 'email' }]
            },
            password: {
                rules: [{
                    type: "minLength[4]"
                }]
            },
            repeat_password: {
                rules: [
                    { type: "minLength[4]" },
                    { type: "match[password]" }
                ]
            }
        }
    });  
    arikaim.ui.form.onSubmit('#create_user_form',function() {
       return usersAdmin.update('#create_user_form');
    },function(result) {       
        arikaim.ui.form.showMessage({
            selector: '#message',
            message: result.message
        });
    });
});