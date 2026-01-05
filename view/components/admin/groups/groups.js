/**
 *  Arikaim
 *  @copyright  Copyright (c)  <info@arikaim.com>
 *  @license    http://www.arikaim.com/license
 *  http://www.arikaim.com
 */
'use strict';

function GroupsAdmin() {
   
    this.add = function(formId, onSuccess, onError) {
        return arikaim.post('/api/users/admin/groups/add',formId,onSuccess,onError);         
    };

    this.update = function(formId, onSuccess, onError) {
        return arikaim.put('/api/users/admin/groups/update',formId,onSuccess,onError);         
    };
    
    this.delete = function(uuid, onSuccess, onError) {
        return arikaim.delete('/api/users/admin/groups/delete/' + uuid,onSuccess,onError);          
    };

    this.addMember = function(groupUuid, userUuid, onSuccess, onError) {
        return arikaim.put('/api/users/admin/groups/add/member',{
            uuid: groupUuid,
            user_uuid : userUuid,            
        },onSuccess,onError);         
    };

    this.removeMember = function(uuid, onSuccess, onError) {
        return arikaim.put('/api/users/admin/groups/remove/member',{
            uuid: uuid
        },onSuccess,onError);         
    };
}

var groupsAdmin = new GroupsAdmin();
