'use strict';

arikaim.component.onLoaded(function() {
    arikaim.ui.button('.create-token',function(element) {
        var type = $(element).attr('type');

        return arikaim.page.loadContent({
            id: 'token_create_content',           
            component: 'users::admin.users.edit.tokens.create',
            params: {
                type: type
            }
        },function(result) {
            
        });
    });
});