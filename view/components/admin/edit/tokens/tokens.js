
function Tokens() {
    var self = this;

    this.init = function() {
        arikaim.ui.button('.delete-token',function(element) {
            var token = $(element).attr('token');
            accessTokens.delete(token,function(result) {
                $('#' + token).remove();
            });
        });
    }
}

var tokens = new Tokens();

$(document).ready(function() { 
    tokens.init();
});