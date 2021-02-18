'use strict';

arikaim.component.onLoaded(function() {
    $('.groups-dropdown').dropdown({       
        onChange: function(value, text, choice) {           
            return arikaim.page.loadContent({
                id: 'group_permissions_list',
                params: { 
                    uuid: value,
                    type: 'group' 
                },
                component: 'users::admin.permissions.relations'
            },function(result) {                                  
            });                     
        }         
    });
});