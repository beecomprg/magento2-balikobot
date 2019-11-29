let config = {
    config: {
        mixins: {
            'Magento_Checkout/js/view/shipping': {
                'Beecom_Balikobot/js/mixin/shipping-mixin': true
            },
            'Magento_Checkout/js/view/shipping-information': {
                'Beecom_Balikobot/js/mixin/shipping-information-mixin': true
            }
        }
    },
    "map": {
        "*": {
            "Magento_Checkout/js/model/shipping-save-processor/default" : "Beecom_Balikobot/js/mixin/default",
            'Magento_Checkout/js/model/checkout-data-resolver': 'Beecom_Balikobot/js/mixin/checkout-data-resolver'
        }
    }
};
