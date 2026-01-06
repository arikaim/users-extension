'use strict';

arikaim.component.onLoaded(function() {
    arikaim.ui.form.onSubmit('#create_user_form',function() {
        return usersAdmin.add('#create_user_form');
    },function(result) {
        arikaim.ui.getComponent('toast').show(result.message);
      
        arikaim.ui.form.clear('#create_user_form');  
        arikaim.events.emit('user.create',result);   
    });
});
