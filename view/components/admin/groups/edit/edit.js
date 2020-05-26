'use strict';

$(document).ready(function() { 
    function initEditGroupForm() {
        arikaim.ui.form.addRules("#group_form");
        arikaim.ui.form.onSubmit('#group_form',function() {
            return groupsAdmin.update('#group_form');
        },function(result) {
            arikaim.ui.form.showMessage({
                message: result.message
            })
        });
    }
   
    $('.groups-dropdown').dropdown({       
        onChange: function(value, text, choice) {           
            return arikaim.page.loadContent({
                id: 'edit_group',
                params: { uuid: value },
                component: 'users::admin.groups.form'
            },function(result) {                  
                initEditGroupForm(); 
            });                     
        }         
    });
  
    initEditGroupForm();    
});
