'use strict';

$(document).ready(function() { 
    arikaim.ui.form.addRules("#group_form");
    arikaim.ui.form.onSubmit('#group_form',function() {
        return groupsAdmin.add('#group_form');
    },function(result) {
        arikaim.ui.form.clear('#group_form');      
        arikaim.ui.form.showMessage({
            message: result.message
        })
    });
});
