$(document).ready(function() { 
    arikaim.ui.form.onSubmit('#signup_form',function() {
        return users.signup('#signup_form');
    },function(result) {             
       callFunction(users.onSignUp,result);
    },function(errors) {
       
    });
});