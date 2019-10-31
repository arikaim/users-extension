/**
 *  Arikaim
 *  
 *  @copyright  Copyright (c) Konstantin Atanasov <info@arikaim.com>
 *  @license    http://www.arikaim.com/license
 *  http://www.arikaim.com
 * 
 */

$(document).ready(function() {
    arikaim.ui.button('#forgotten_button',function(element) {
        return arikaim.page.loadContent({
            id : 'login_panel',
            component: 'users::reset-password'
        });
    });
});