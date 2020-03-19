'use strict';

$(document).ready(function() {     
    arikaim.ui.form.addRules("#permission_form",{
        inline: false,
        fields: {
            name: {
                rules: [{ type: 'minLength[2]' }]
            }           
        }
    });  
});