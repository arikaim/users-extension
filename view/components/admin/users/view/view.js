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

        withObject('order',function(order) {
            order.init('users_rows','users::admin.users.view.rows','users');
        });
      
        paginator.init('users_rows',"users::admin.users.view.rows",'users');    

        search.init({
            id: 'users_rows',
            component: 'users::admin.users.view.rows',
            event: 'user.search.load'
        },'users')  
        
        $('.status-filter').dropdown({          
            onChange: function(value) {      
                var searchData = {
                    search: {
                        status: value,                       
                    }          
                }              
                search.setSearch(searchData,'users',function(result) {                  
                    self.loadList();
                });               
            }
        });

        arikaim.events.on('user.search.load',function(result) {      
            paginator.reload();
            self.initRows();    
        },'userSearch');                   
    };

    this.loadList = function() {        
        arikaim.page.loadContent({
            id: 'users_rows',         
            component: 'users::admin.users.view.rows'
        },function(result) {
            self.initRows();  
            paginator.reload(); 
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
            arikaim.ui.setActiveTab('#edit_user','.users-tab-item');

            return arikaim.page.loadContent({
                id: 'users_content',
                params: { uuid: uuid },
                component: 'users::admin.users.edit'
            });
        });

        $('.status-dropdown').dropdown({
            onChange: function(value) {               
                var uuid = $(this).attr('uuid');
                usersAdmin.setStatus(uuid,value);
            }
        });      

        arikaim.ui.button('.delete-button',function(element) {
            var uuid = $(element).attr('uuid');
            var title = $(element).attr('data-title');
            var message = arikaim.ui.template.render(self.getMessage('remove.content'),{ title: title });

            return modal.confirmDelete({ 
                title: self.getMessage('remove.title'),
                description: message
            },function() {
                usersAdmin.delete(uuid,function(result) {
                    arikaim.ui.table.removeRow('#row_' + uuid);     
                });
            });
        });
    };
}

var usersView = createObject(UsersView,ControlPanelView);

arikaim.component.onLoaded(function() {
    usersView.init();
    usersView.initRows();
});