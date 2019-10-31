/**
 *  Arikaim
 *  
 *  @copyright  Copyright (c) Konstantin Atanasov <info@arikaim.com>
 *  @license    http://www.arikaim.com/license
 *  http://www.arikaim.com
 *  
 *  Extension: users
 *  Component: users::admin.users 
 */

function UsersAdmin() {

    this.add = function(form_id, onSuccess, onError) {
        return arikaim.post('/api/users/admin/add',form_id,onSuccess,onError);         
    };

    this.update = function(form_id, onSuccess, onError) {
        return arikaim.put('/api/users/admin/update',form_id,onSuccess,onError);         
    };
    
    this.changePassword = function(form_id, onSuccess, onError) {
        return arikaim.put('/api/users/admin/change-password',form_id,onSuccess,onError);         
    };

    this.setStatus = function(uuid, status, onSuccess, onError) { 
        var data = { status: status, uuid: uuid };
        return arikaim.put('/api/users/admin/status',data,onSuccess,onError);           
    };

    this.delete = function(uuid, onSuccess, onError) {
        return arikaim.delete('/api/users/admin/' + uuid,onSuccess,onError);          
    };

    this.init = function() {
        arikaim.ui.tab();
    };
}

var usersAdmin = new UsersAdmin();

$(document).ready(function() {
    usersAdmin.init();
});