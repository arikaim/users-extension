$(document).ready(function() {
    arikaim.ui.form.onSubmit('#create_user_form',function() {
       return users.changeDetails({ formId: '#create_user_form' });
    },function(result) {       
        arikaim.ui.form.showMessage({
            selector: '#message',
            message: result.message
        });
    });
});