'use strict';

arikaim.component.onLoaded(function() {
    arikaim.ui.button('.remove-avatar',function(element) {
        var uuid = $(element).attr('uuid');
        return users.deleteAvatar(uuid,function(result) {
            arikaim.page.loadContent({
                id: 'avatar_image',
                params: { },
                component: 'users::admin.users.edit.avatar.image'
            },function(result) {

            });
        })       
    });
});