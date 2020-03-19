/**
 *  Arikaim
 *  @copyright  Copyright (c) Konstantin Atanasov <info@arikaim.com>
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
    }   

    this.init = function() {
        arikaim.ui.tab('.permissions-tab-item','permissions_content');
    };
}

var permissions = new PermissionsAdmin();

$(document).ready(function() {
    permissions.init();
});