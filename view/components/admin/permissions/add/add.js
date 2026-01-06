'use strict';

arikaim.component.onLoaded(function() {
    arikaim.ui.button('.add-permission-button',function(element) {
        var uuid = $(element).attr('uuid');
        var type = $(element).attr('type');
        var permissionUuid = $('.permissions-dropdown').val();  

        permissions.grantPermission(uuid,permissionUuid,type,function(result) {
            return arikaim.page.loadContent({
                id: 'permissions_list',
                params: { 
                    uuid: result.uuid,
                    relation_id: result.relation_id,
                    type: type
                },
                append: true,
                component: 'users::admin.permissions.relations.item'
            },function(result) {                  
                permissions.initItems();
            });    
        },function(error) {
            arikaim.ui.getComponent('toast').show(error);
        });       
    });
});