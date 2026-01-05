'use strict';

arikaim.component.onLoaded(function() {
    arikaim.ui.button('.add-group-member',function(element) {
        var groupUuid = $(element).attr('uuid');
        var userUuid = '' ;//$('.users-dropdown').dropdown('get value');

        groupsAdmin.addMember(groupUuid,userUuid,function(result) {           
            return arikaim.page.loadContent({
                id: 'group_members_list',
                params: { 
                    uuid: result.uuid
                },
                append: true,
                component: 'users::admin.groups.members.list.item'
            },function(result) {                  
                membersAdmin.initItems();
            });    
        },function(error) {
            arikaim.page.toastMessage({
                class: 'error',
                message: error
            });
        });       
    });
});