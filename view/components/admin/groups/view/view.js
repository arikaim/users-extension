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
        this.loadMessages('users::admin')

        arikaim.ui.loadComponentButton('.create-group');

        arikaim.events.on('groups.update',function(data) {
            self.loadRow(data.uuid,true,false);
        },'onGroupUpdate');

        arikaim.events.on('groups.create',function(data) {
            self.loadRow(data.uuid,false,true);
        },'onGroupCreate'); 
        
        this.initRows();
        console.log('init');
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
        arikaim.ui.loadComponentButton('.group-action');

        arikaim.ui.button('.delete-button',function(element) {
            var uuid = $(element).attr('uuid');
            var title = $(element).attr('data-title');
            var message = arikaim.ui.template.render(self.getMessage('group_remove.content'),{ title: title });

            arikaim.ui.getComponent('confirm_delete').open(function() {
                groupsAdmin.delete(uuid,function(result) {
                    arikaim.ui.table.removeRow('#row_' + uuid);     
                },function(error) {
                    arikaim.ui.getComponent('toast').show(error);                          
                });
            },message);            
        });
    };
}

var userGroupsView = createObject(UserGroupsView,ControlPanelView);

arikaim.component.onLoaded(function() {
    userGroupsView.init();
});