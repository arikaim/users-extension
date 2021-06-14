'use strict';

arikaim.component.onLoaded(function() {
    arikaim.ui.button('.create-token',function(element) {
        return arikaim.page.loadContent({
            id: 'token_create_content',           
            component: 'users::admin.users.edit.tokens.create'
        },function(result) {
            
        });
    });
});