'use strict';

arikaim.component.onLoaded(function() {
    arikaim.ui.form.addRules("#permission_form");  
    arikaim.ui.form.onSubmit('#permission_form',function() {
        return permissions.add('#permission_form');
    },function(result) {
        arikaim.ui.form.clear('#permission_form');      
        arikaim.ui.form.showMessage({
            message: result.message
        })
    });
});
