/**
 *  Arikaim
 *  @copyright  Copyright (c) Konstantin Atanasov <info@arikaim.com>
 *  @license    http://www.arikaim.com/license
 *  http://www.arikaim.com
 */
'use strict';

function UserGroupsView() {
    var self = this;

    this.init = function() {
        paginator.init('group_rows',"users::admin.groups.view.rows",'groups');                
    };

    this.initRows = function() {
        var component = arikaim.component.get('users::admin');
        var removeMessage = component.getProperty('messages.group_remove.content');

        arikaim.ui.button('.edit-button',function(element) {
            var uuid = $(element).attr('uuid');
            arikaim.ui.setActiveTab('#edit_group','.groups-tab-item');

            return arikaim.page.loadContent({
                id: 'groups_content',
                params: { uuid: uuid },
                component: 'users::admin.groups.edit'
            });
        });

        arikaim.ui.button('.permissions-button',function(element) {
            var uuid = $(element).attr('uuid');
            arikaim.ui.setActiveTab('#group_permissions_tab','.groups-tab-item');

            return arikaim.page.loadContent({
                id: 'groups_content',
                params: { uuid: uuid },
                component: 'users::admin.groups.permissions'
            });
        });

        arikaim.ui.button('.delete-button',function(element) {
            var uuid = $(element).attr('uuid');
            var title = $(element).attr('data-title');
            var message = arikaim.ui.template.render(removeMessage,{ title: title });

            return modal.confirmDelete({ 
                title: component.getProperty('messages.remove.title'),
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

var userGroupsView = new UserGroupsView();

$(document).ready(function() {
    userGroupsView.init();
});