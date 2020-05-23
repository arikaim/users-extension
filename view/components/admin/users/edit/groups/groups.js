'use strict';

$(document).ready(function() {
    $('.groups-dropdown').dropdown({});

    initGroupList();
    
    arikaim.ui.button('.add-group-button',function(element) {
        var groupUuid = $('.groups-dropdown').dropdown('get value');
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
                arikaim.page.toastMessage(result.message);               
            },function(error) { 
                arikaim.page.toastMessage({
                    class: 'error',
                    message: error
                });                 
            });
        });
    }   
});