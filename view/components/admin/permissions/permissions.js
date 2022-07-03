/**
 *  Arikaim
 *  @copyright  Copyright (c)  <info@arikaim.com>
 *  @license    http://www.arikaim.com/license
 *  http://www.arikaim.com
 */
'use strict';

function PermissionsAdmin() {
    var self = this;

    this.add = function(formId, onSuccess, onError) {
        return arikaim.post('/api/users/admin/permission/add',formId,onSuccess,onError);         
    };

    this.update = function(formId, onSuccess, onError) {
        return arikaim.put('/api/users/admin/permission/update',formId,onSuccess,onError);         
    };    

    this.delete = function(uuid, onSuccess, onError) {
        return arikaim.delete('/api/users/admin/permission/delete/' + uuid,onSuccess,onError);         
    }; 

    this.grantPermission = function(uuid, permissionUuid, type, onSuccess, onError) {
        type = getDefaultValue(type,'user');
        var data = {
            uuid: permissionUuid,
            target_uuid : uuid,      
            type: type       
        }

        return arikaim.put('/api/users/admin/permission/grant',data,onSuccess,onError);         
    };
    
    this.denyPermission = function(uuid, onSuccess, onError) {      
        var data = {
            uuid: uuid                    
        }

        return arikaim.put('/api/users/admin/permission/deny',data,onSuccess,onError);         
    };

    this.updatePermissionType = function(uuid, type, actionType, onSuccess, onError) {      
        var data = {
            uuid: uuid,
            type: type,
            actionType: actionType                
        }

        return arikaim.put('/api/admin/users/permission/type',data,onSuccess,onError);         
    };

    this.initItems = function() {
        arikaim.ui.button('.delete-permission',function(element) {           
            var uuid = $(element).attr('uuid');
            return permissions.denyPermission(uuid,function(result) {             
                $('#item_' + uuid).remove();
                arikaim.page.toastMessage(result.message);
            },function(error) {              
                arikaim.page.toastMessage({
                    class: 'error',
                    message: error
                });                
            });
        });

        arikaim.ui.button('.change-permission-type',function(element) {           
            var uuid = $(element).attr('uuid');
            var type = $(element).attr('type');
            var actionType = $(element).attr('action-type');

            return permissions.updatePermissionType(uuid,type,actionType,function(result) {       
                var remove = (actionType == 'remove') ? 0 : 1;

                arikaim.page.loadContent({
                    id: 'permission_type_content_' + type + '_' + uuid,
                    params: { 
                        uuid: uuid,
                        type: type,
                        remove: remove
                    },
                    component: 'users::admin.permissions.label.button'
                },function(result) {
                    self.initItems();
                });

                arikaim.page.toastMessage(result.message);
            },function(error) {              
                arikaim.page.toastMessage({
                    class: 'error',
                    message: error
                });                
            });
        });
       
    }   

    this.init = function() {
        arikaim.ui.tab('.permissions-tab-item','permissions_content');
    };
}

var permissions = new PermissionsAdmin();

arikaim.component.onLoaded(function() {
    permissions.init();
});