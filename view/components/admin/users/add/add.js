'use strict';

arikaim.component.onLoaded(function() {
    arikaim.ui.form.addRules("#create_user_form",{
        inline: false,
        fields: {
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
        arikaim.page.toastMessage(result.message);
        arikaim.ui.form.clear('#create_user_form');  
        arikaim.events.emit('user.create',result);   

        arikaim.ui.getComponent('user_create_panel').close();
    });
});
