/**
 *  Arikaim
 *  @copyright  Copyright (c)  <info@arikaim.com>
 *  @license    http://www.arikaim.com/license
 *  http://www.arikaim.com
 */
'use strict';

function UserSettings() {    

    this.init = function() {      
       arikaim.ui.tab('.settings-tab-item','settings_tab')
    };

    this.getSignupSettings = function() {
        return {
            name: {
                show: $('#show_name').prop('checked'),
                required: $('#require_name').prop('checked'),
            },
            email: {
                show: $('#show_email').prop('checked'),
                required: $('#require_email').prop('checked'),
            },
            username: {
                show: $('#show_username').prop('checked'),
                required: $('#require_username').prop('checked'),
            },
            phone: {
                show: $('#show_phone').prop('checked'),
                required: $('#require_phone').prop('checked'),
            },
            captcha: {
                show: $('#show_captcha').prop('checked'),
            }
        };
    };   
}

var userSettings = new UserSettings();

arikaim.component.onLoaded(function() {
    userSettings.init();
})