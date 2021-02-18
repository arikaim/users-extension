'use strict';

arikaim.component.onLoaded(function() {
    $('.user-type-dropdown').dropdown({
        onChange: function(value) {
            console.log(value);
            options.save('users.default.type',value);
        }   
    });
});