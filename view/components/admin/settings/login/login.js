'use strict';

arikaim.component.onLoaded(function() {
    $('.login-with').checkbox({
        onChange: function() {
            var value = $(this).val();
            options.save('users.login.with',value);
        }   
    });

    $('#captcha_protect').checkbox({
        onChecked: function() {
            options.save('users.login.captcha.protect',true);
        },
        onUnchecked: function() {
            options.save('users.login.captcha.protect',false);
        }   
    });

    arikaim.ui.form.onSubmit('#settings_form',function() {
        var redirectUrl = $('#redirect').val();
        return options.save('users.login.redirect',redirectUrl);
    },function(result) {
        arikaim.ui.form.showMessage(result.message);
    });

    $('#require_verified_email').checkbox({
        onChecked: function() {
            options.save('users.login.require.verified.email',true);
        },
        onUnchecked: function() {
            options.save('users.login.require.verified.email',false);
        }   
    });
});