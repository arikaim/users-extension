/**
 *  Arikaim  
 *  @copyright  Copyright (c) Konstantin Atanasov <info@arikaim.com>
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
        var data = {
            uuid: uuid
        };

        return arikaim.put('/api/users/admin/restore',data,onSuccess,onError);
    };

    this.init = function() {
        this.loadMessages('users::admin.trash');

        arikaim.ui.button('.empty-trash',function(element) {       
            return modal.confirmDelete({ 
                title:  self.getMessage('empty.title'),
                description: self.getMessage('empty.description') 
            },function() {         
                self.emptyTrash(function(result) {
                    self.loadRows();
                },function(error) {
                    arikaim.page.toastMessage({
                        class: 'error',
                        message: error
                    });
                });
            });               
        });
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
                arikaim.page.toastMessage(result.message);                   
            },function(error) {
                arikaim.page.toastMessage({
                    class: 'error',
                    message: error
                });
            });
        });   
    };
}

var trashView = createObject(TrashView,ControlPanelView);

arikaim.component.onLoaded(function() {   
    trashView.init();
    trashView.initRows();
});
