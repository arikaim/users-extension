/**
 *  Arikaim
 *  @copyright  Copyright (c) Konstantin Atanasov <info@arikaim.com>
 *  @license    http://www.arikaim.com/license
 *  http://www.arikaim.com
 */
'use strict';

function PermissionsView() {
    var self = this;

    this.initRows = function() {
        var component = arikaim.component.get('users::admin');
        var removeMessage = component.getProperty('messages.permisison_remove.content');

        arikaim.ui.button('.edit-permission',function(element) {           
            var uuid = $(element).attr('uuid');
            arikaim.ui.setActiveTab('#edit_permisisons_tab','.permissions-tab-item');

            return arikaim.page.loadContent({
                id: 'permissions_content',
                params: { uuid: uuid },
                component: 'users::admin.permissions.view.edit'
            });
        });

        arikaim.ui.button('.delete-permission',function(element) {           
            var uuid = $(element).attr('uuid');

            var title = $(element).attr('data-title');
            var message = arikaim.ui.template.render(removeMessage,{ title: title });

            return modal.confirmDelete({ 
                title: component.getProperty('messages.remove.title'),
                description: message
            },function() {
                permissions.delete(uuid,function(result) {             
                    $('#item_' + uuid).remove();
                    arikaim.page.toastMessage(result.message);
                },function(error) {              
                    arikaim.page.toastMessage({
                        class: 'error',
                        message: error
                    });                
                });
            });

        });
    }   
}

var permissionsView = new PermissionsView();
