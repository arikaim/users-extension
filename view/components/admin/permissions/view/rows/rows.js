'use strict';

arikaim.component.onLoaded(function() {
    safeCall('permissionsView',function(obj) {
        obj.initRows();
    },true);    
}); 