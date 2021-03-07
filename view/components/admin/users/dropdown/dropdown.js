'use strict';

arikaim.component.onLoaded(function() {
    var dataField = $('.users-dropdown').attr('data-field');
    
    $('.users-dropdown').dropdown({
        apiSettings: {     
            on: 'now',      
            url: arikaim.getBaseUrl() + '/api/users/admin/list/' + dataField + '/{query}',   
            cache: false        
        },       
        filterRemoteData: false         
    });
});