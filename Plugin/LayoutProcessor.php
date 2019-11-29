<?php


namespace Beecom\Balikobot\Plugin;


class LayoutProcessor
{
    /**
     * @param array $jsLayout
     */
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        $jsLayout
    ) {
        $validation['required-entry'] = true;

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['balikobot-delivery-fields']['children']['balikobot_type'] = [
            'component' => "Magento_Ui/js/form/element/abstract",
            'config' => [
                'customScope' => 'balikobotDeliveryFields',
                'template' => 'ui/form/field',
                'elementTmpl' => "Beecom_Balikobot/form/input",
                'id' => "balikobot_type"
            ],
            'dataScope' => 'balikobotDeliveryFields.balikobot_type',
            'label' => "Input option",
            'provider' => 'checkoutProvider',
            'visible' => true,
            'validation' => $validation,
            'sortOrder' => 2,
            'id' => 'balikobot_type',
            'additionalClasses' => 'hide-input-delivery'
        ];

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['balikobot-delivery-fields']['children']['balikobot_branch'] = [
            'component' => "Magento_Ui/js/form/element/abstract",
            'config' => [
                'customScope' => 'balikobotDeliveryFields',
                'template' => 'ui/form/field',
                'elementTmpl' => "Beecom_Balikobot/form/input",
                'id' => "balikobot_branch"
            ],
            'dataScope' => 'balikobotDeliveryFields.balikobot_branch',
            'label' => "Input option",
            'provider' => 'checkoutProvider',
            'visible' => true,
            'validation' => $validation,
            'sortOrder' => 2,
            'id' => 'balikobot_branch',
            'additionalClasses' => 'hide-input-delivery'
        ];

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['balikobot-delivery-fields']['children']['balikobot_additional_info'] = [
            'component' => "Magento_Ui/js/form/element/abstract",
            'config' => [
                'customScope' => 'balikobotDeliveryFields',
                'template' => 'ui/form/field',
                'elementTmpl' => "Beecom_Balikobot/form/input",
                'id' => "balikobot_additional_info"
            ],
            'dataScope' => 'balikobotDeliveryFields.balikobot_additional_info',
            'label' => "Input option",
            'provider' => 'checkoutProvider',
            'visible' => true,
            'validation' => $validation,
            'sortOrder' => 2,
            'id' => 'balikobot_additional_info',
            'additionalClasses' => 'hide-input-delivery'
        ];

        return $jsLayout;
    }
}
