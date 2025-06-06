/**
 *  Arikaim
 *  @copyright  Copyright (c)  <info@arikaim.com>
 *  @license    http://www.arikaim.com/license
 *  http://www.arikaim.com
 */
'use strict';

function PermissionsView() {
    var self = this;

    this.init = function() {
        this.loadMessages('users::admin');
        arikaim.ui.loadComponentButton('.create-permission');

        arikaim.events.on('permission.update',function(data) {
            self.loadRow(data.uuid,true,false);
        },'onPermisionUpdate');

        arikaim.events.on('permission.create',function(data) {
            self.loadRow(data.uuid,false,true);
        },'onPermisionCreate');

    };

    this.loadRow = function(uuid,replace, append) {
        arikaim.ui.loadComponent({
            id: (replace) == true ? 'item_' + uuid : 'permissions_rows',
            append: append,
            replace: replace,
            params: { 
                uuid: uuid 
            },
            component: 'users::admin.permissions.view.item'
        },function(result) {
            self.initRows();
        });
    };
    
    this.initRows = function() {
        arikaim.ui.button('.edit-permission',function(element) {           
            var uuid = $(element).attr('uuid');
            arikaim.ui.setActiveTab('#edit_permisisons_tab','.permissions-tab-item');

            return arikaim.page.loadContent({
                id: 'permissions_content',
                params: { uuid: uuid },
                component: 'users::admin.permissions.edit'
            });
        });

        arikaim.ui.button('.delete-permission',function(element) {           
            var uuid = $(element).attr('uuid');
            var title = $(element).attr('data-title');
            var message = arikaim.ui.template.render(self.getMessage('permisison_remove.content'),{ title: title });

            return modal.confirmDelete({ 
                title: self.getMessage('permisison_remove.title'),
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

var permissionsView = createObject(PermissionsView,ControlPanelView);

arikaim.component.onLoaded(function() {
    permissionsView.init();
});