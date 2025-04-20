'use strict';

arikaim.component.onLoaded(function() {
    safeCall('userGroupsView',function(obj) {
        obj.initRows();
    },true);    
}); 