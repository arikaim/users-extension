/**
 *  Arikaim
 *  
 *  @copyright  Copyright (c) Konstantin Atanasov <info@arikaim.com>
 *  @license    http://www.arikaim.com/license
 *  http://www.arikaim.com
 * 
*/

$(document).ready(function () {    
    arikaim.ui.button('#login_page_link',function(element) {
        arikaim.page.loadContent({
            id : 'reset_password_panel',
            component: 'users::login'
        });
    });
});