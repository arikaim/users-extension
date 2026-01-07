'use strict';

arikaim.component.onLoaded(function() {
    arikaim.ui.button('.remove-avatar',function(element) {
        return users.deleteAvatar(function(result) {
            arikaim.page.loadContent({
                id: 'avatar_image',
                params: { avatar: null },
                component: 'users::admin.users.edit.avatar.image'
            });
        })       
    });
});