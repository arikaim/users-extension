'use strict';

arikaim.component.onLoaded(function() {
    arikaim.ui.button('.delete-token',function(element) {
        var uuid = $(element).attr('uuid');

        return modal.confirmDelete({ 
            title: 'Confirm',
            description: 'Remove token '
        },function() {
            apiTokens.delete(uuid,function(result) {
                arikaim.ui.table.removeRow('#' + uuid); 
            });
        });
    });    
});