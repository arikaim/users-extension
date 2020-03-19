'use strict';

$(document).ready(function() {  
    safeCall('permissionsView',function(obj) {
        obj.initRows();
    },true);    
}); 