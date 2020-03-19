'use strict';

$(document).ready(function() {
    $('.settings-checkbox').checkbox({
        onChange: function() {
            var checked = $(this).prop('checked'); 
            var name = $(this).attr('name');                    
            options.save(name,checked);
        }   
    });
});