$(document).ready(function() {  
    $('.users-dropdown').dropdown({
        apiSettings: {     
            on: 'now',      
            url: arikaim.getBaseUrl() + '/api/users/admin/list/{query}',   
            cache: false        
        },
        onChange: function(value, text, choice) {
            if (isEmpty(value) == true) {
                $('#edit_user_menu').html('');
            } else {
                return arikaim.page.loadContent({
                    id: 'edit_user_menu',
                    params: { uuid: value },
                    component: 'users::admin.users.edit.menu'
                },function(result) {
                    arikaim.ui.tab('.edit-tab-item','edit_user_content');
                });
            }           
        },
        filterRemoteData: false         
    });
});