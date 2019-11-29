define([
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/cart/cache',
    'Magento_Checkout/js/model/checkout-data-resolver'
], function ($, quote, cartCache, checkoutDataResolver) {
    'use strict';

    return function (Component) {
        return Component.extend({
            initialize: function () {

                this._super();
            },

            getDeliveryPoint: function () {
                if (!quote.shippingMethod()) {
                    return '';
                }
                if (!quote.shippingMethod()['extension_attributes']) {
                    return '';
                }
                let shippingMethod = quote.shippingMethod().method_code+'_'+quote.shippingMethod().carrier_code;
                let selectedMethodLabel = $('#label_method_visible_' + shippingMethod).text();
                if (!selectedMethodLabel) {
                    let quoteData = window.checkoutConfig.quoteData;

                    return quoteData.balikobot_additional_info;
                }

                return selectedMethodLabel;
            }
        });
    };
});
