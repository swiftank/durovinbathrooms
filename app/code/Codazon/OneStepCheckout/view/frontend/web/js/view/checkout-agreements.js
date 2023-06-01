/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'jquery',
    'uiComponent',
    'Codazon_OneStepCheckout/js/model/agreements-modal',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/set-shipping-information',
    'Magento_Checkout/js/action/set-billing-address',
    'Magento_SalesRule/js/action/set-coupon-code',
    'Magento_Ui/js/modal/alert'
], function(ko, $, Component, agreementsModal, customer, quote, setShippingInformationAction, setBillingAddress, setCouponCodeAction, alert) {
    'use strict';

    var checkoutConfig = window.checkoutConfig,
        agreementManualMode = 1,
        agreementsConfig = checkoutConfig ? checkoutConfig.checkoutAgreements : {};

    return Component.extend({
        defaults: {
            template: 'Codazon_OneStepCheckout/checkout/checkout-agreements'
        },
        isVisible: agreementsConfig.isEnabled,
        agreements: agreementsConfig.agreements,
        modalTitle: ko.observable(null),
        modalContent: ko.observable(null),
        modalWindow: null,
        isPlaceOrderActionAllowed: ko.observable(false),
        updatedAddressText: ko.observable(false),

        /**
         * Checks if agreement required
         *
         * @param {Object} element
         */
        isAgreementRequired: function(element) {
            return element.mode == agreementManualMode; //eslint-disable-line eqeqeq
        },

        /**
         * Show agreement content in modal
         *
         * @param {Object} element
         */
        showContent: function(element) {
            this.modalTitle(element.checkboxText);
            this.modalContent(element.content);
            agreementsModal.showModal();
        },

        /**
         * build a unique id for the term checkbox
         *
         * @param {Object} context - the ko context
         * @param {Number} agreementId
         */
        getCheckboxId: function(context, agreementId) {
            var paymentMethodName = '',
                paymentMethodRenderer = context.$parents[1];

            // corresponding payment method fetched from parent context
            if (paymentMethodRenderer) {
                // item looks like this: {title: "Check / Money order", method: "checkmo"}
                paymentMethodName = paymentMethodRenderer.item ?
                    paymentMethodRenderer.item.method : '';
            }

            return 'agreement_' + paymentMethodName + '_' + agreementId;
        },

        /**
         * Init modal window for rendered element
         *
         * @param {Object} element
         */
        initModal: function(element) {
            agreementsModal.createModal(element);
        },

        changeHandler: function(data, event) {
            $("#checkout-payment-method-load input[name='agreement[" + data.agreementId + "]']").click();
            $(event.target).next().click();
            return true;
        },
        validateShipping: function() {
            if (!quote.isVirtual()) {
                var shippingMethod = quote.shippingMethod();
                if (shippingMethod.method_code == 'pickup') {
                    console.log(quote.shippingAddress());
                    if (quote.shippingAddress().firstname != "") {
                        return true;
                    } else {
                        alert({
                            content: $.mage.__('Please select a store.'),
                            actions: {
                                always: function() {}
                            }
                        });
                    }
                } else {
                    return requirejs('uiRegistry').get('checkout.steps.shipping-step.shippingAddress').validateShippingInformation();
                }
            } else {
                return true;
            }

        },
        validateForm: function(form) {
            return $(form).validation() && $(form).validation('isValid');
        },
        validatePayment: function() {
            if ($(".payment-method._active").length == 0) {
                alert({
                    content: $.mage.__('Please specify a payment method.'),
                    actions: {
                        always: function() {}
                    }
                });
                return false;
            } else {
                return true;
            }

        },
        placeOrder: function() {
            var self = this;
            /** 
             * Remove already appeared errors 
             * author: Ankush
            */
            $('.message.message-error.error').remove();
            if (this.validateForm('#checkout_agreements_block') && this.validatePayment() && this.validateShipping()) {
                this.isPlaceOrderActionAllowed(true);
                this.updatedAddressText(false);
                if ($('#submit-shipping-method').length) { //if not virtual product
                    var shippingMethod = quote.shippingMethod();
                    if (shippingMethod.method_code == 'pickup') {

                    } else {
                        if ($("#co-payment-form ._active input[type='checkbox']").prop('checked') == true || $("#co-payment-form .billing-address-same-as-shipping-block input[type='checkbox']").prop('checked') == true) {
                            /** 
                             * For Shipping Address Not updated on signin customers
                             * author: Ankush
                            */
                            if (checkoutConfig.isCustomerLoggedIn) {
                                quote.billingAddress(quote.shippingAddress()) 
                            } else {
                                quote.billingAddress(checkoutConfig.shippingAddress); //update shippingAddress to billing address on payment step, this trigger update text on address of payment step
                            }
                        }
                    }

                    $(".payment-method._active").find('.action.primary.checkout').trigger('click');
                     var billingDetails = $('.billing-address-details');
                    if( billingDetails.length == 0){
                        this.updatedAddressText(true);
                    }

                    setTimeout(() => {
                        var afterClickErr = $('.payment-methods .message.message-error.error');
                        if (afterClickErr.length > 0 ||  billingDetails.length == 0) {
                            this.isPlaceOrderActionAllowed(false);
                        }
                    }, 1500);

                    /** 
                     * For checking error on declined card
                     * author: Ankush
                    */
                    var checkingError = setInterval(function(){
                        if ($('.message.message-error.error').length > 0) {
                            self.isPlaceOrderActionAllowed(false);
                            clearInterval(checkingError);
                        }
                        }, 10000);  

                } else {
                    $(".payment-method._active").find('.action.primary.checkout').trigger('click');
                }
            }
        }
    });
});