'use strict';

arikaim.component.onLoaded(function() {
    arikaim.ui.form.addRules("#group_form");
    
    arikaim.ui.form.onSubmit('#group_form',function() {
        return groupsAdmin.add('#group_form');
    },function(result) {
        arikaim.events.emit('groups.create',result);

        arikaim.ui.getComponent('toast').show(result.message);
        arikaim.ui.getComponent('group_create_panel').close();
    });
});
