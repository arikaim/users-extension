'use strict';

arikaim.component.onLoaded(function() {
    arikaim.ui.form.onSubmit('#token_form',function() {
        return apiTokens.create('#token_form');
     },function(result) {       
         arikaim.ui.form.showMessage({
             selector: '#message',
             message: result.message
        });

        $('#token_create_content').html('');

        return arikaim.page.loadContent({
            id: 'tokens_view',    
            params: { uuid: result.user_uuid },       
            component: 'users::admin.users.edit.tokens.view'
        });
     });
});