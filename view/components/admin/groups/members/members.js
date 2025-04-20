/**
 *  Arikaim
 *  @copyright  Copyright (c)  <info@arikaim.com>
 *  @license    http://www.arikaim.com/license
 *  http://www.arikaim.com
 */
'use strict';

function MembersAdmin() {
  
    this.initItems = function() {
        arikaim.ui.button('.remove-group-member',function(element) {           
            var uuid = $(element).attr('uuid');
        
            return groupsAdmin.removeMember(uuid,function(result) {             
                $('#item_' + uuid).remove();
                arikaim.page.toastMessage(result.message);
            },function(error) {              
                arikaim.page.toastMessage({
                    class: 'error',
                    message: error
                });                
            });
        });
    };
}

var membersAdmin = new MembersAdmin();

arikaim.component.onLoaded(function() {
   
});