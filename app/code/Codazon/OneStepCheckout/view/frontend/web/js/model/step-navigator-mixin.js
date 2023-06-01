define([
    'mage/utils/wrapper',
    'jquery'
], function (wrapper, $) {
    'use strict';

    let mixin = {
        isProcessed: function (originalFn) {
            if(window.checkoutConfig.is_osc_enabled){
                return true;
            }else{
                return originalFn();
            }
        }
    };

    return function (target) {
        return wrapper.extend(target, mixin);
    };
});
