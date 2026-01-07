/**
 *  Arikaim  
 *  @copyright  Copyright (c)  <info@arikaim.com>
 *  @license    http://www.arikaim.com/license
 *  http://www.arikaim.com
 */
'use strict';

function TrashView() {
    var self = this;

    this.emptyTrash = function(onSuccess, onError) {
        return arikaim.delete('/api/users/admin/trash/empty',onSuccess,onError);
    };
    
    this.restoreUser = function(uuid, onSuccess, onError) {
        return arikaim.put('/api/users/admin/restore',{
            uuid: uuid
        },onSuccess,onError);
    };

    this.init = function() {
        this.loadMessages('users::admin.trash');

        arikaim.ui.button('.empty-trash',function(element) {       
            arikaim.ui.getComponent('confirm_delete').open(function() {         
                self.emptyTrash(function(result) {
                    self.loadRows();
                },function(error) {
                    arikaim.ui.getComponent('toast').show(error);      
                });
            },self.getMessage('empty.description'));                   
        });

        this.initRows();
    };

    this.loadRows = function() {
        return arikaim.page.loadContent({
            id: 'items_rows',           
            component: 'users::admin.users.view.rows',
            params: { show_deleted: true }
        },function(result) {
            self.initRows();
        }); 
    };

    this.initRows = function() {
        arikaim.ui.button('.restore-button',function(element) {   
            var uuid = $(element).attr('uuid');

            trashView.restoreUser(uuid,function(result) {
                arikaim.ui.table.removeRow('#row_' + uuid,null,function(element) {
                    $('.trash-button').addClass('disabled');
                });                           
                arikaim.ui.getComponent('toast').show(result.message);                       
            },function(error) {
                arikaim.ui.getComponent('toast').show(error);      
            });
        });   
    };
}

var trashView = createObject(TrashView,ControlPanelView);

arikaim.component.onLoaded(function() {   
    trashView.init();   
});
