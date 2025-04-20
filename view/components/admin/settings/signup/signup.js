'use strict';

arikaim.component.onLoaded(function() {
    $('.from-settings').checkbox({
        onChange: function() {
            var settings = userSettings.getSignupSettings();
            options.save('users.signup.form',settings);
        }   
    });

    $('.settings-checkbox').checkbox({
        onChange: function() {
            var value = $(this).val();
            var name = $(this).attr('name');                    
            options.save(name,value);
        }   
    });

    arikaim.ui.form.onSubmit('#settings_form',function() {
        var redirectUrl = $('#redirect').val();
        return options.save('users.signup.redirect',redirectUrl);
    },function(result) {
        arikaim.ui.form.showMessage(result.message);
    });
});