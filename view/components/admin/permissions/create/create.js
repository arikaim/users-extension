'use strict';

arikaim.component.onLoaded(function() {
    arikaim.ui.form.onSubmit('#permission_form',function() {
        return permissions.add('#permission_form');
    },function(result) {
        arikaim.ui.form.clear('#permission_form'); 
        
        arikaim.events.emit('permission.create',result);
        arikaim.page.toastMessage(result.message);
        arikaim.ui.getComponent('permission_create_panel').close();
    });
});
