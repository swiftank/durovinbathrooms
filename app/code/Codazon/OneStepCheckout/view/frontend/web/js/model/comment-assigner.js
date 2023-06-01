/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*global alert*/
define([
    'jquery',
    'Magento_Checkout/js/model/quote'
], function ($, quote) {
    'use strict';
    /** Override default place order action and add agreement_ids to request */
    return function (paymentData) {
        var comments = jQuery('[name="comment-code"]:first').val()

        paymentData['comments'] = comments;
        paymentData['shippingAddress'] = checkoutConfig.shippingAddress;
    };
});
