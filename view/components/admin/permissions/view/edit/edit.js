'use strict';

arikaim.component.onLoaded(function() {
    initEditPermissionForm();

    $('.permissions-dropdown').dropdown({
        onChange: function(value, text, choice) {
            console.log(value);

            return arikaim.page.loadContent({
                id: 'edit_permission',
                params: { uuid: value },
                component: 'users::admin.permissions.view.form'
            },function(result) {
                initEditPermissionForm();
            });    
        },
    });

    function initEditPermissionForm() {
        arikaim.ui.form.addRules("#permission_form");  
        arikaim.ui.form.onSubmit('#permission_form',function() {
            return permissions.update('#permission_form');
        },function(result) {         
            arikaim.ui.form.showMessage({
                message: result.message
            })
        });
    }   
});
