'use strict';

arikaim.component.onLoaded(function() {
    arikaim.ui.button('.delete-token',function(element) {
        var uuid = $(element).attr('uuid');
        accessTokens.delete(uuid,function(result) {
            arikaim.ui.table.removeRow('#' + uuid); 
        });
    });
});