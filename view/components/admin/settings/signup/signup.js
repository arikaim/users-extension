'use strict';

arikaim.component.onLoaded(function() {
    $('.from-settings').on('change', function() {
        var settings = userSettings.getSignupSettings();
        options.save('users.signup.form',settings);      
    });

    $('.settings-checkbox').on('change', function() {
        var value = $(this).val();
        var name = $(this).attr('name');                    
        options.save(name,value);         
    });

    arikaim.ui.form.onSubmit('#settings_form',function() {      
        return options.save('users.signup.redirect',$('#redirect').val());
    },function(result) {
        arikaim.ui.form.showMessage(result.message);
    });
});