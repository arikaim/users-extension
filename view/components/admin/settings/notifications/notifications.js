'use strict';

arikaim.component.onLoaded(function() {
    $('.settings-checkbox').on('change', function() {
        var checked = $(this).prop('checked'); 
        var name = $(this).attr('name'); 

        options.save(name,checked);       
    });
});