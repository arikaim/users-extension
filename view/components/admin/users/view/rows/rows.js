'use strict';

arikaim.component.onLoaded(function() {
    safeCall('usersView',function(obj) {
        obj.initRows();
    },true);    
}); 