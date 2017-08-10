/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'uiComponent',
    'underscore',
    'Magento_Customer/js/customer-data',
    'jquery/jquery-storageapi'
], function ($, Component, _, customerData) {
    'use strict';

    return Component.extend({
        defaults: {
            cookieMessages: [],
            messages: []
        },
        /** @inheritdoc */
        initialize: function () {
            var tempMessages = null;
            this._super();

            this.cookieMessages = $.cookieStorage.get('mage-messages');
            this.messages = customerData.get('messages').extend({
                disposableCustomerData: 'messages'
            });

            tempMessages = this.cookieMessages;

            if (!_.isEmpty(this.messages().messages)) {
                customerData.set('messages', {});
            }

            $.cookieStorage.set('mage-messages', '');

            setTimeout(function () {
//                alert("removing here");
                $(".messages").hide('blind', {}, 500);
            }, 5000);
            
            setTimeout(function () {
                console.log("messages");
                console.log(tempMessages.length);
                if(tempMessages.length)
                    window.scrollTo(0, document.body.scrollHeight);                
            }, 1500);
        }
    });
});
