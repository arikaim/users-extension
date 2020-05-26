'use strict';

$(document).ready(function() {
    arikaim.ui.form.addRules("#change_password_form");
    arikaim.ui.form.onSubmit('#change_password_form',function() {
       return usersAdmin.changePassword('#change_password_form');
    },function(result) {       
        arikaim.ui.form.showMessage({
            selector: '#message',
            message: result.message
        });
    });
});