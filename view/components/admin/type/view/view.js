/**
 *  Arikaim
 *  @copyright  Copyright (c) Konstantin Atanasov <info@arikaim.com>
 *  @license    http://www.arikaim.com/license
 *  http://www.arikaim.com
 */
'use strict';

function UserTypeView() {
    
    this.initRows = function() {
        arikaim.ui.button('.view-options',function(element) {
            $('.view-options').removeClass('blue');
            $(element).addClass('blue');

            var uuid = $(element).attr('uuid');
        
            return arikaim.page.loadContent({
                id: 'user_type_options',               
                component: 'users::admin.type.options', 
                params: { uuid: uuid },
            });
        });
    };
}

var userTypeView = new UserTypeView();

arikaim.component.onLoaded(function() { 
    userTypeView.initRows();
});