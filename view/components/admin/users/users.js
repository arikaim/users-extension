/**
 *  Arikaim
 *  @copyright  Copyright (c)  <info@arikaim.com>
 *  @license    http://www.arikaim.com/license
 *  http://www.arikaim.com
 */
'use strict';

function UsersAdmin() {
    
    this.add = function(formId, onSuccess, onError) {
        return arikaim.post('/api/users/admin/add',formId,onSuccess,onError);         
    };

    this.update = function(formId, onSuccess, onError) {
        return arikaim.put('/api/users/admin/update',formId,onSuccess,onError);         
    };
    
    this.changePassword = function(formId, onSuccess, onError) {
        return arikaim.put('/api/users/admin/change-password',formId,onSuccess,onError);         
    };

    this.setStatus = function(uuid, status, onSuccess, onError) { 
        var data = { 
            status: status,
            uuid: uuid 
        };
        
        return arikaim.put('/api/users/admin/status',data,onSuccess,onError);           
    };

    this.delete = function(uuid, onSuccess, onError) {
        return arikaim.delete('/api/users/admin/delete/' + uuid,onSuccess,onError);          
    };

    this.deleteAvatar = function(uuid, onSuccess, onError) {
        return arikaim.delete('/api/users/admin/avatar/delete/' + uuid,onSuccess,onError);          
    };   
}

var usersAdmin = new UsersAdmin();

arikaim.component.onLoaded(function() {
    arikaim.ui.tab('.users-tab-item','users_content');
});