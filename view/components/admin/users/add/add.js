'use strict';

arikaim.component.onLoaded(function() {
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
        return usersAdmin.add('#create_user_form');
    },function(result) {
        arikaim.ui.form.clear('#create_user_form');      
        arikaim.ui.form.showMessage({
            message: result.message
        })
    });
});
