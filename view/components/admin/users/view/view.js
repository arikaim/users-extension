/**
 *  Arikaim
 *  @copyright  Copyright (c) Konstantin Atanasov <info@arikaim.com>
 *  @license    http://www.arikaim.com/license
 *  http://www.arikaim.com
 */
'use strict';

function UsersView() {
    var self = this;

    this.init = function() {
        order.init('users_rows','users::admin.users.view.rows','users');
        paginator.init('users_rows',"users::admin.users.view.rows",'users');         
        search.init({
            id: 'users_rows',
            component: 'users::admin.users.view.rows',
            event: 'user.search.load'
        },'users')  
        
        arikaim.events.on('user.search.load',function(result) {      
            paginator.reload();
            self.initRows();    
        },'userSearch');                   
    };

    this.initRows = function() {
        var component = arikaim.component.get('users::admin');
        var removeMessage = component.getProperty('messages.remove.content');

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
            var message = arikaim.ui.template.render(removeMessage,{ title: title });

            return modal.confirmDelete({ 
                title: component.getProperty('messages.remove.title'),
                description: message
            },function() {
                usersAdmin.delete(uuid,function(result) {
                    arikaim.ui.table.removeRow('#row_' + uuid);     
                });
            });
        });
    };
}

var usersView = new UsersView();

$(document).ready(function() {
    usersView.init();
    usersView.initRows();
});