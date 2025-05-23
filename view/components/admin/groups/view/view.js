/**
 *  Arikaim
 *  @copyright  Copyright (c)  <info@arikaim.com>
 *  @license    http://www.arikaim.com/license
 *  http://www.arikaim.com
 */
'use strict';

function UserGroupsView() {
    var self = this;

    this.init = function() {
        arikaim.ui.loadComponentButton('.create-group');
        this.loadMessages('users::admin')

        arikaim.events.on('groups.update',function(data) {
            self.loadRow(data.uuid,true,false);
        },'onGroupUpdate');

        arikaim.events.on('groups.create',function(data) {
            self.loadRow(data.uuid,false,true);
        },'onGroupCreate');

        paginator.init('group_rows','users::admin.groups.view.rows','groups');                
    };

    this.loadRow = function(uuid,replace, append) {
        arikaim.ui.loadComponent({
            id: (replace) == true ? 'row_' + uuid : 'group_rows',
            append: append,
            replace: replace,
            params: { 
                uuid: uuid 
            },
            component: 'users::admin.groups.view.item'
        },function(result) {
            self.initRows();
        });
    };

    this.initRows = function() {
        arikaim.ui.button('.edit-button',function(element) {
            var uuid = $(element).attr('uuid');
           
            return arikaim.page.loadContent({
                id: 'groups_content',
                params: { uuid: uuid },
                component: 'users::admin.groups.edit'
            });
        });

        arikaim.ui.button('.members-button',function(element) {
            var uuid = $(element).attr('uuid');
           
            return arikaim.page.loadContent({
                id: 'groups_content',
                params: { uuid: uuid },
                component: 'users::admin.groups.members'
            });
        });

        arikaim.ui.button('.permissions-button',function(element) {
            var uuid = $(element).attr('uuid');
        
            return arikaim.page.loadContent({
                id: 'groups_content',
                params: { uuid: uuid },
                component: 'users::admin.groups.permissions'
            });
        });

        arikaim.ui.button('.delete-button',function(element) {
            var uuid = $(element).attr('uuid');
            var title = $(element).attr('data-title');
            var message = arikaim.ui.template.render(self.getMessage('group_remove.content'),{ title: title });

            return modal.confirmDelete({ 
                title: self.getMessage('group_remove.title'),
                description: message
            },function() {
                groupsAdmin.delete(uuid,function(result) {
                    arikaim.ui.table.removeRow('#row_' + uuid);     
                },function(error) {
                    arikaim.page.toastMessage({
                        class: 'error',
                        message: error
                    });  
                });
            });
        });
    };
}

var userGroupsView = createObject(UserGroupsView,ControlPanelView);

arikaim.component.onLoaded(function() {
    userGroupsView.init();
});