$(document).ready(function() { 
    arikaim.ui.viewPasswordButton('.view-password');
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
});