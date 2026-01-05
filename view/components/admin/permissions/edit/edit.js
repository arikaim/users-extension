'use strict';

arikaim.component.onLoaded(function() {
    arikaim.ui.form.onSubmit('#permission_form',function() {
        return permissions.update('#permission_form');
    },function(result) {         
        arikaim.ui.form.showMessage({
            message: result.message
        });

        arikaim.events.emit('permission.update',result);
    });
});
