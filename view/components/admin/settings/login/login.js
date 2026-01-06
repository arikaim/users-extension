'use strict';

arikaim.component.onLoaded(function() {
    $('.login-with').on('change', function(event) {
        var val = $(this).val();
        options.save('users.login.with',val);
    });

    $('#captcha_protect').on('change', function(event) {     
        options.save('users.login.captcha.protect',event.currentTarget.checked);
    });

    $('#require_verified_email').on('change', function(event) {        
        options.save('users.login.require.verified.email',event.currentTarget.checked);          
    });
   
    arikaim.ui.form.onSubmit('#settings_form',function() {
        var redirectUrl = $('#redirect').val();
        return options.save('users.login.redirect',redirectUrl);
    },function(result) {
        arikaim.ui.form.showMessage(result.message);
    });

});