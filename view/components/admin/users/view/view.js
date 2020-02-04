/**
 *  Arikaim
 *  @copyright  Copyright (c) Konstantin Atanasov <info@arikaim.com>
 *  @license    http://www.arikaim.com/license
 *  http://www.arikaim.com
 */

function UsersView() {
    var self = this;

    this.init = function() {
        paginator.init('users_rows',"users::admin.view.rows",'users');         
        
        $('.actions').dropdown({});        
        search.init('users',null,{ id: 'users_rows', component_name: 'users::admin.view.rows' });

        arikaim.ui.button('#select_all',function(element) {      
            arikaim.ui.selectAll(element);                
        });         
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
});