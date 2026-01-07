/**
 *  Arikaim
 *  @copyright  Copyright (c)  <info@arikaim.com>
 *  @license    http://www.arikaim.com/license
 *  http://www.arikaim.com
 */
'use strict';

function UsersView() {
    var self = this;
       
    this.init = function() {
        this.loadMessages('users::admin');

        arikaim.ui.loadComponentButton('.add-user');

        withObject('order',function(order) {
            order.init('users_rows','users::admin.users.view.rows','users');
        });
      
        search.init({
            id: 'users_rows',
            component: 'users::admin.users.view.rows',
            event: 'user.search.load'
        },'users')  
        
        $('.status-filter').on('change', function() { 
            var val = $(this).val();    
        
            search.setSearch({
                search: {
                    status: val,                       
                }          
            },'users',function(result) {                  
                self.loadList();
            });               
        });

        arikaim.events.on('user.search.load',function(result) {           
            self.initRows();    
        },'userSearch');   
        
        arikaim.events.on('user.update',function(data) {
            self.loadItem(data.uuid,true,false);
        },'onUserUpdate');

        arikaim.events.on('user.create',function(data) {
            self.loadItem(data.uuid,false,true);
        },'onUserCreate');      
    };

    this.loadItem = function(uuid,replace,append) {
        arikaim.ui.loadComponent({
            id: (replace) == true ? 'row_' + uuid : 'users_rows',
            append: append,
            replace: replace,
            params: { 
                uuid: uuid 
            },
            component: 'users::admin.users.view.item'
        },function(result) {
            self.initRows();
        });
    };

    this.loadList = function() {        
        arikaim.page.loadContent({
            id: 'users_rows',         
            component: 'users::admin.users.view.rows'
        },function(result) {
            self.initRows();          
        });
    };

    this.initRows = function() {
        arikaim.ui.button('.user-details-button',function(element) {
            var uuid = $(element).attr('uuid');

            return arikaim.page.loadContent({
                id: 'users_content',
                params: { uuid: uuid },
                component: 'users::admin.users.details'
            });
        });

        arikaim.ui.button('.edit-button',function(element) {
            var uuid = $(element).attr('uuid');
         
            return arikaim.page.loadContent({
                id: 'users_content',
                params: { uuid: uuid },
                component: 'users::admin.users.edit'
            });
        });

        $('.status-dropdown').on('change', function() {
            var val = $(this).val();
            var uuid = $(this).attr('uuid');
            
            usersAdmin.setStatus(uuid,val,function(result) {
                //arikaim.ui.getComponent('toast').show(result.message);
            });
        });      

        arikaim.ui.button('.delete-button',function(element) {
            var uuid = $(element).attr('uuid');
            var title = $(element).attr('data-title');
            var message = arikaim.ui.template.render(self.getMessage('remove.content'),{ title: title });

            arikaim.ui.getComponent('confirm_delete').open(function() {
                usersAdmin.delete(uuid,function(result) {
                    arikaim.ui.table.removeRow('#row_' + uuid);     
                });
            },message);
        });
    };
}

var usersView = createObject(UsersView,ControlPanelView);

arikaim.component.onLoaded(function() {
    usersView.init();   
});