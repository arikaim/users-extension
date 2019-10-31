
$(document).ready(function() {
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
});