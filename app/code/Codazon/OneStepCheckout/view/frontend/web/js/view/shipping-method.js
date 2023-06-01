/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'Magento_Ui/js/form/form',
    'ko',
    'Magento_Customer/js/model/customer',
    'Magento_Customer/js/model/address-list',
    'Magento_Checkout/js/model/address-converter',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/create-shipping-address',
    'Magento_Checkout/js/action/select-shipping-address',
    'Magento_Checkout/js/model/shipping-rates-validator',
    'Magento_Checkout/js/model/shipping-address/form-popup-state',
    'Magento_Checkout/js/model/shipping-service',
    'Magento_Checkout/js/action/select-shipping-method',
    'Magento_Checkout/js/model/shipping-rate-registry',
    'Magento_Checkout/js/action/set-shipping-information',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Ui/js/modal/modal',
    'Magento_Checkout/js/model/checkout-data-resolver',
    'Magento_Checkout/js/checkout-data',
    'uiRegistry',
    'mage/translate',
    'Magento_Checkout/js/action/select-billing-address',
    'Magento_Checkout/js/model/shipping-rate-service'
], function (
    $,
    _,
    Component,
    ko,
    customer,
    addressList,
    addressConverter,
    quote,
    createShippingAddress,
    selectShippingAddress,
    shippingRatesValidator,
    formPopUpState,
    shippingService,
    selectShippingMethodAction,
    rateRegistry,
    setShippingInformationAction,
    stepNavigator,
    modal,
    checkoutDataResolver,
    checkoutData,
    registry,
    $t,
    selectBillingAddress
) {
    'use strict';

    var popUp = null;
    var isSavingShipping = false;
    var isSetDefaultShippingMethod = false;

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/shipping',
            shippingFormTemplate: 'Magento_Checkout/shipping-address/form',
            shippingMethodListTemplate: 'Magento_Checkout/shipping-address/shipping-method-list',
            shippingMethodItemTemplate: 'Magento_Checkout/shipping-address/shipping-method-item'
        },
        default_shipping_carrier: ko.observable(window.checkoutConfig.default_shipping),
        visible: ko.observable(!quote.isVirtual()),
        errorValidationMessage: ko.observable(false),
        isCustomerLoggedIn: customer.isLoggedIn,
        isFormPopUpVisible: formPopUpState.isVisible,
        isFormInline: addressList().length === 0,
        isNewAddressAdded: ko.observable(false),
        saveInAddressBook: 1,
        quoteIsVirtual: quote.isVirtual(),

        initObservable: function () {
        	var lastSelectedBillingAddress = null,
		        newAddressOption = {
		            /**
		             * Get new address label
		             * @returns {String}
		             */
		            getAddressInline: function () {
		                return $t('New Address');
		            },
		            customerAddressId: null
		        },
		        addressOptions = addressList().filter(function (address) {
		            return address.getType() == 'customer-address'; //eslint-disable-line eqeqeq
		        });

		    addressOptions.push(newAddressOption);

            this._super().
                observe(['paymentGroupsList']);
            this._super()
                .observe({
                    isAddressSameAsShipping: true,
                    isAddressFormVisible: !customer.isLoggedIn() || addressOptions.length === 1,
                    isAddressDetailsVisible: quote.billingAddress() != null,
                });

            return this;
        },

        /**
         * @return {exports}
         */
        initialize: function () {
            var self = this,
                hasNewAddress,
                fieldsetName = 'checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset';
            this._super();
            
            self.hasShippingMethod = ko.pureComputed(function(){
                var hasMethod = false;
                if(quote.shippingMethod()){
                    var stillAvailable = self.isShippingOnList(quote.shippingMethod().carrier_code,quote.shippingMethod().method_code);
                    hasMethod = (stillAvailable)?true:false;
                }
                return hasMethod;
            }),
            shippingService.getShippingRates().subscribe(function(){
                var shippingMethod = self.getDefaultMethod();
                if(!isSetDefaultShippingMethod){
                    self.selectShippingMethod(shippingMethod);
                }
            });
            quote.shippingMethod.subscribe(function () {//this function trigger when changing shipping address (country, region, postcode) and when select shipping method
                self.errorValidationMessage(false);
                if(!quote.isVirtual()){
                    $('.checkout-index-index .loading-mask').css('z-index',0);
                    $('.checkout-index-index .loading-mask').css('opacity',0);
                    if(!isSavingShipping){
                        isSavingShipping = true;
                        setShippingInformationAction().done(function(response){
                            $('.checkout-index-index .loading-mask').css('z-index',999);
                            $('.checkout-index-index .loading-mask').css('opacity',1);
                            self.afterSaveShipping();
                            isSavingShipping = false;
                        });//save seleted shipping method and update payment method by ajax
                    }
                    
                }
            });
            
            quote.shippingAddress.subscribe(function(address){
                if(address['extension_attributes']){
                    if(address['extension_attributes']['pickup_location_code']){ //trigger after select pickup store
                        setShippingInformationAction().done(function(response){
                            self.afterSaveShipping();
                        });
                    }
                }
            });

            return this;
        },

        afterSaveShipping: function(){
            var shippingMethod = quote.shippingMethod();
            if(shippingMethod && shippingMethod['method_code'] == 'pickup'){
                $("#opc-shipping_method").hide();
            }else{
                quote.billingAddress(quote.shippingAddress()); //update billing address below payment method
                $("#opc-shipping_method").show();
            }
        },

        isShippingOnList: function(carrier_code,method_code){
            var list = this.getShippingList();
            if(list.length > 0){
                var carrier = ko.utils.arrayFirst(list, function(carrier) {
                    return (carrier.code == carrier_code);
                });
                if(carrier && carrier.methods.length > 0){
                    var method = ko.utils.arrayFirst(carrier.methods, function(method) {
                        return (method.method_code == method_code);
                    });
                    return (method)?true:false;
                }else{
                    return false;
                }
            }
            return false;
        },

        getShippingList: function () {
            var list = [];
            var rates = this.rates();
            if(rates && rates.length > 0){
                ko.utils.arrayForEach(rates, function(method) {
                    if(list.length > 0){
                        var notfound = true;
                        ko.utils.arrayForEach(list, function(carrier) {
                            if(carrier && carrier.code == method.carrier_code){
                                carrier.methods.push(method);
                                notfound = false;
                            }
                        });
                        if(notfound == true){
                            var carrier = {
                                code:method.carrier_code,
                                title:method.carrier_title,
                                methods:[method]
                            }
                            list.push(carrier);
                        }
                    }else{
                        var carrier = {
                            code:method.carrier_code,
                            title:method.carrier_title,
                            methods:[method]
                        }
                        list.push(carrier);
                    }
                });
            }
            return list;
        },

        getDefaultMethod: function(){
            if(quote.shippingMethod()){
                return false;
            }
            var self = this;
            var list = this.getShippingList();
            if(list.length > 0){
                /*var carrier = ko.utils.arrayLast(list, function(data) {
                    return (self.default_shipping_carrier())?(data.code == self.default_shipping_carrier()):true;
                });*/
                var carrier = list.pop();

                if(carrier && carrier.methods.length > 0){
                    var method = ko.utils.arrayFirst(carrier.methods, function() {
                        return true;
                    });
                    return (method)?method:false;
                }else{
                    return false;
                }
            }
            return false;
        },

        /**
         * Navigator change hash handler.
         *
         * @param {Object} step - navigation step
         */
        navigate: function (step) {
            step && step.isVisible(true);
        },

        /**
         * Shipping Method View
         */
        rates: shippingService.getShippingRates(),
        isLoading: shippingService.isLoading,
        isSelected: ko.computed(function () {
            return quote.shippingMethod() ?
                quote.shippingMethod()['carrier_code'] + '_' + quote.shippingMethod()['method_code'] :
                null;
        }),

        /**
         * @param {Object} shippingMethod
         * @return {Boolean}
         */
        selectShippingMethod: function (shippingMethod) { // trigger when click on shipping method and after change shipping address
            isSetDefaultShippingMethod = true;
            selectShippingMethodAction(shippingMethod);
            checkoutData.setSelectedShippingRate(shippingMethod['carrier_code'] + '_' + shippingMethod['method_code']);
            
            if(shippingMethod['method_code'] == 'pickup'){
                quote.billingAddress(null);
            }else{
                if($("#co-payment-form ._active input[type='checkbox']").prop('checked') == true || $("#co-payment-form .billing-address-same-as-shipping-block input[type='checkbox']").prop('checked') == true){
                    quote.billingAddress(quote.shippingAddress());//update shippingAddress to billing address on payment step, this trigger update text on address of payment step
                }
            }

            return true;
        },

        //billing-address function
        updateAddresses: function () {
            if (window.checkoutConfig.reloadOnBillingAddress ||
                !window.checkoutConfig.displayBillingOnPaymentMethod
            ) {
                setBillingAddressAction(globalMessageList);
            }
        },

        isAddressSameAsShipping: function(){
            if($("#co-payment-form ._active input[type='checkbox']").prop('checked') == true || $("#co-payment-form .billing-address-same-as-shipping-block input[type='checkbox']").prop('checked') == true){
                return true;
            }else{
                return false;
            }
        },

        //====== end of billing-address function ====

        /**
         * Set shipping information handler
         */
        setShippingInformation: function () { //handle "next" button on default checkout to payment step

        },
        validateShipping: function(){
            return requirejs('uiRegistry').get('checkout.steps.shipping-step.shippingAddress').validateShippingInformation();
        },
    });
});
