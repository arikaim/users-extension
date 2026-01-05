'use strict';

arikaim.component.onLoaded(function() {
    arikaim.ui.form.onSubmit('#create_user_form',function() {
       return usersAdmin.update('#create_user_form');
    },function(result) {       
        arikaim.ui.form.showMessage({
            message: result.message
        });

        arikaim.events.emit('user.update',result);
    });
});