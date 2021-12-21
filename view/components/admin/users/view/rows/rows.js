'use strict';

arikaim.component.onLoaded(function(component) {
    safeCall('usersView',function(obj) {
        obj.initRows();
    },true);    
}); 