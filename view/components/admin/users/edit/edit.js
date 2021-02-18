'use strict';

arikaim.component.onLoaded(function() {
    $('.users-dropdown').on('change',function() {
        var selected = $('.users-dropdown').dropdown("get value");

        if (isEmpty(selected) == true) {
            $('#edit_user_menu').html('');
        } else {
            return arikaim.page.loadContent({
                id: 'edit_user_menu',
                params: { uuid: selected },
                component: 'users::admin.users.edit.menu'
            },function(result) {
                arikaim.ui.tab('.edit-tab-item','edit_user_content');
            });
        }                   
    });
    arikaim.ui.tab('.edit-tab-item','edit_user_content');
});