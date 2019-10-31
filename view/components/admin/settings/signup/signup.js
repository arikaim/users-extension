
$(document).ready(function() {
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
});