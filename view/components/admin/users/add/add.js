'use strict';

$(document).ready(function() { 
    arikaim.ui.form.onSubmit('#create_user_form',function() {
        return usersAdmin.add('#create_user_form');
    },function(result) {
        arikaim.ui.form.clear('#create_user_form');      
        arikaim.ui.form.showMessage({
            message: result.message
        })
    });
});
