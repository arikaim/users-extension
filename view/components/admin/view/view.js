/**
 *  Arikaim
 *  
 *  @copyright  Copyright (c) Konstantin Atanasov <info@arikaim.com>
 *  @license    http://www.arikaim.com/license
 *  http://www.arikaim.com
 * 
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
        arikaim.ui.button('.edit-button',function(element) {
            var uuid = $(element).attr('uuid');
            return arikaim.page.loadContent({
                id: 'tab_content',
                params: { uuid: uuid },
                component: 'users::admin.edit'
            });
        });

        $('.status-dropdown').dropdown({
            onChange: function(value) {               
                var uuid = $(this).attr('uuid');
                usersAdmin.setStatus(uuid,value);
            }
        });      
    };
}

var usersView = new UsersView();

$(document).ready(function() {
    usersView.init();
});