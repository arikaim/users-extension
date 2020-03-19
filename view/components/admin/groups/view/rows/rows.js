'use strict';

$(document).ready(function() {  
    safeCall('userGroupsView',function(obj) {
        obj.initRows();
    },true);    
}); 