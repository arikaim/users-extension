$(document).ready(function() {  
    safeCall('usersView',function(obj) {
        obj.initRows();
    },true);    
}); 