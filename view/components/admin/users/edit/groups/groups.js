'use strict';

arikaim.component.onLoaded(function() {
    initGroupList();
    
    arikaim.ui.button('.add-group-button',function(element) {
        var groupUuid = $('.groups-dropdown').val();
        var userUuid = $(element).attr('uuid');

        groupsAdmin.addMember(groupUuid,userUuid,function(result) {
            $('#no_groups_message').remove();
            return arikaim.page.loadContent({
                id : 'groups_list',
                params: {
                    uuid: groupUuid,
                    relation_uuid: result.uuid
                },
                append: true,
                component: 'users::admin.users.edit.groups.item'
            },function(result) {
                initGroupList(); 
            });
        },function(error) {
            initGroupList(); 
        });
    });

    function initGroupList() {
        arikaim.ui.button('.remove-group-member',function(element) {
            var uuid = $(element).attr('uuid');
            var relationUuid = $(element).attr('relation-uuid');

            groupsAdmin.removeMember(relationUuid,function(result) {               
                $('#item_' + uuid).remove();              
                arikaim.ui.getComponent('toast').show(result.message);             
            },function(error) { 
                arikaim.ui.getComponent('toast').show(error);                                 
            });
        });
    }   
});