/**
 *  Arikaim  
 *  @copyright  Copyright (c)  <info@arikaim.com>
 *  @license    http://www.arikaim.com/license
 *  http://www.arikaim.com
 */
'use strict';

function UserApiTokens() {
   
    this.create = function(formId, onSuccess, onError) {
        return arikaim.post('/api/users/token/create',formId, onSuccess,onError);          
    };

    this.setStatus = function(formId, onSuccess, onError) {
        return arikaim.put('/api/users/token/status',formId,onSuccess,onError);
    };

    this.delete = function(uuid, onSuccess, onError) {
        return arikaim.put('/api/users/token/delete',{
            uuid: uuid
        },onSuccess,onError);
    };
}

var apiTokens = new UserApiTokens();
