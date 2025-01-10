'use strict';

arikaim.component.onLoaded(function() {
    function initEditGroupForm() {
        arikaim.ui.form.addRules("#group_form");
        arikaim.ui.form.onSubmit('#group_form',function() {
            return groupsAdmin.update('#group_form');
        },function(result) {
            arikaim.ui.form.showMessage({
                message: result.message
            });

            arikaim.events.emit('groups.update',result);
        });
    }

    initEditGroupForm();    
});
