'use strict';

$(document).ready(function() {
    $('.user-type-dropdown').dropdown({
        onChange: function(value) {
            console.log(value);
            options.save('users.default.type',value);
        }   
    });
});