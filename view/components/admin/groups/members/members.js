/**
 *  Arikaim
 *  @copyright  Copyright (c) Konstantin Atanasov <info@arikaim.com>
 *  @license    http://www.arikaim.com/license
 *  http://www.arikaim.com
 */
'use strict';

function MembersAdmin() {
    var self = this;

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
    
    this.init = function() {
        $('.groups-dropdown').dropdown({       
            onChange: function(value, text, choice) {           
                return arikaim.page.loadContent({
                    id: 'group_members',
                    params: { 
                        uuid: value
                    },
                    component: 'users::admin.groups.members.list'
                },function(result) {                  
                    
                });                     
            }         
        });    
    };
}

var membersAdmin = new MembersAdmin();

$(document).ready(function() { 
    membersAdmin.init();    
});