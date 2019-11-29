define(
    [
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
        'Magento_Checkout/js/model/shipping-rate-service',
        "Beecom_Balikobot/js/select2.full.min"
    ],function (
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
        $t) {
        'use strict';

        let mixin = {
            initObservable: function () {
                this._super();

                this.selectedMethod = ko.computed(function() {
                    let method = quote.shippingMethod();
                    return method != null ? method.carrier_code + '_' + method.method_code : null;
                }, this);

                return this;
            },
            selectShippingMethod: function (shippingMethod) {
                let shippingMethodField = shippingMethod;

                if (shippingMethod.extension_attributes) {
                    if (shippingMethod.extension_attributes.additional_component) {
                        if (shippingMethod.extension_attributes.additional_component[1]) {
                            jQuery.ajax({
                                url: '/balikobot/branches/index',
                                type: 'POST',
                                dataType: 'json',
                                data: {deliverer: shippingMethod.extension_attributes.additional_component[0], shippingMethod: shippingMethodField.method_code},
                                showLoader: true //use for display loader
                            }).done(function (data) {
                                $('<div id="delivery-point-modal" />').html(data.html)
                                    .modal({
                                        title: $.mage.__('Choose delivery point'),
                                        autoOpen: true,
                                        closed: function () {
                                            // on close
                                            $('#delivery-point-modal').remove();
                                        },
                                        buttons: [{
                                            text: $.mage.__('Confirm selection'),
                                            attr: {
                                                'data-action': 'confirm'
                                            },
                                            'class': 'primary',
                                            click: function () {
                                                let select2 = $('#balikobot_select');
                                                let deliveryModal = $('#delivery-point-modal');
                                                let select2selected = $('#balikobot_select :selected').text();

                                                let shippingMethod = shippingMethodField;
                                                let selectedMethod = $('td#label_method_' + shippingMethod.method_code + '_' + shippingMethod.carrier_code);
                                                let balikobotType = $("input[name=balikobot_type]", selectedMethod);
                                                let balikobotBranch = $("input[name=balikobot_branch]", selectedMethod);
                                                let balikobotAdditional = $("input[name=balikobot_additional_info]", selectedMethod);
                                                let errorMessage = $('.delivery-point-error', selectedMethod);
                                                errorMessage.hide();
                                                balikobotType.val(shippingMethod.extension_attributes.additional_component[0]);
                                                balikobotBranch.val(select2.val());
                                                balikobotAdditional.val(select2selected);
                                                balikobotBranch.keyup();
                                                balikobotType.keyup();
                                                balikobotAdditional.keyup();

                                                selectShippingMethodAction(shippingMethod);
                                                checkoutData.setSelectedShippingRate(shippingMethod['carrier_code'] + '_' + shippingMethod['method_code']);

                                                deliveryModal.modal('closeModal');
                                                deliveryModal.remove();

                                                $('.delivery-point-label').text('');

                                                let selectedMethodLabel = $('#label_method_visible_' + shippingMethod.method_code + '_' + shippingMethod.carrier_code);
                                                selectedMethodLabel.html(select2selected + '<span class="pl-1 primary--text edit-balikobot-selection">' + $t('edit') + '</span>');

                                                return true;
                                            }
                                        }]
                                    });

                                $('#balikobot_select').select2({
                                    width: '100%'
                                });
                            });
                        } else {
                            let shippingMethod = shippingMethodField;
                            let selectedMethod = $('td#label_method_' + shippingMethod.method_code + '_' + shippingMethod.carrier_code);
                            let balikobotType = $("input[name=balikobot_type]", selectedMethod);
                            let balikobotBranch = $("input[name=balikobot_branch]", selectedMethod);
                            let balikobotAdditional = $("input[name=balikobot_additional_info]", selectedMethod);
                            let errorMessage = $('.delivery-point-error', selectedMethod);
                            errorMessage.hide();
                            balikobotType.val(shippingMethod.extension_attributes.additional_component[0]);
                            balikobotBranch.val('');
                            balikobotAdditional.val(shippingMethod.method_title);
                            balikobotBranch.keyup();
                            balikobotType.keyup();
                            balikobotAdditional.keyup();

                            selectShippingMethodAction(shippingMethod);
                            checkoutData.setSelectedShippingRate(shippingMethod['carrier_code'] + '_' + shippingMethod['method_code']);
                        }

                    }
                } else {
                      selectShippingMethodAction(shippingMethod);
                      checkoutData.setSelectedShippingRate(shippingMethod['carrier_code'] + '_' + shippingMethod['method_code']);

                      return true;
                }
            },
            setShippingInformation: function () {
                if (this.validateCustomFieldsShipping() && this.validateShippingInformation()) {
                    setShippingInformationAction().done(
                        function () {
                            stepNavigator.next();
                        }
                    );
                }
            },
            validateCustomFieldsShipping: function () {
                if (!quote.shippingMethod()) {
                    this.errorValidationMessage($t('Please specify a shipping method.'));

                    return false;
                }

                if (!quote.shippingMethod().extension_attributes) {
                    return true;
                }

                let shippingMethod = quote.shippingMethod().method_code + '_' + quote.shippingMethod().carrier_code;
                let selectedMethod = $('td#label_method_' + shippingMethod);

                let errorMessage = $('.delivery-point-error', selectedMethod);
                errorMessage.hide();
                if (this.source.get('balikobotDeliveryFields') && quote.shippingMethod().carrier_code === 'matrixrate' && quote.shippingMethod().extension_attributes.additional_component[1]) {
                    this.source.set('params.invalid', false);
                    this.source.trigger('balikobotDeliveryFields.data.validate');
                    if(this.source.get('params.invalid')) {
                        errorMessage.show();
                        return false;
                    }
                }
                return true;
            }
        };

        return function (target) { // target == Result that Magento_Ui/.../default returns.
            return target.extend(mixin); // new result that all other modules receive
        };
    });
