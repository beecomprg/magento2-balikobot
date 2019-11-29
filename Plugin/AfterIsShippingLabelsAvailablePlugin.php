<?php
namespace Beecom\Balikobot\Plugin;

class AfterIsShippingLabelsAvailablePlugin
{
    public function afterIsShippingLabelsAvailable(\Beecom\MatrixRate\Model\Carrier\Matrixrate $carrierModel, $result)
    {
        return true;
    }
}
