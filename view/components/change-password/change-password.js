$(document).ready(function() {
    arikaim.ui.form.onSubmit('#change_password_form',function() {    
       return users.changePassword('#change_password_form');
    },function(result) {
        arikaim.ui.form.showMessage({
            selector: '#change_password_form',
            message: result.message,
            hide: 0
        });
    });
});