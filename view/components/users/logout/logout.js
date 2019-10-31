
$(document).ready(function() {
    users.logout(function(result) {
        var redirect_url = (isEmpty(result.redirect_url) == true) ? '/' : result.redirect_url;
        arikaim.page.load(redirect_url);
    });
});