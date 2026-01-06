'use strict';

arikaim.component.onLoaded(function() {
    $('.user-type-dropdown').on('chnage', function() {
        var val = $(this).val();
        options.save('users.default.type',val);       
    });
});