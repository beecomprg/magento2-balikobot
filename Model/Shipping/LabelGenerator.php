<?php


namespace Beecom\Balikobot\Model\Shipping;


use Magento\Framework\App\RequestInterface;

class LabelGenerator extends \Magento\Shipping\Model\Shipping\LabelGenerator
{
    /**
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @param RequestInterface $request
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Pdf_Exception
     */
    public function create(\Magento\Sales\Model\Order\Shipment $shipment, RequestInterface $request)
    {
        $order = $shipment->getOrder();
        $carrier = $this->carrierFactory->create($order->getShippingMethod(true)->getCarrierCode());
        if (!$carrier->isShippingLabelsAvailable()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Shipping labels is not available.'));
        }
        $shipment->setPackages($request->getParam('packages'));
        $response = $this->labelFactory->create()->requestToShipment($shipment);
        if ($response->hasErrors()) {
            $errors = $response->getErrors();
            throw new \Magento\Framework\Exception\LocalizedException(__($errors[0]));
        }
        if (!$response->hasInfo()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Response info is not exist.'));
        }
        $labelsContent = [];
        $trackingNumbers = [];
        $info = $response->getInfo();
        $packages = $response->getPackages();
        $shipment->setPackages($packages);
        foreach ($info as $inf) {
            if (!empty($inf['tracking_number']) && !empty($inf['label_content'])) {
                $labelsContent[] = $inf['label_content'];
                $trackingNumbers[] = $inf['tracking_number'];
            }
        }
        $outputPdf = $this->combineLabelsPdf($labelsContent);
        $shipment->setShippingLabel($outputPdf->render());
        $carrierCode = $carrier->getCarrierCode();
        $carrierTitle = $this->scopeConfig->getValue(
            'carriers/' . $carrierCode . '/title',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $shipment->getStoreId()
        );
        if ($order->getBalikobotType()) {
            $carrierTitle = $shipment->getOrder()->getShippingDescription();
        }
        if (!empty($trackingNumbers)) {
            $this->addTrackingNumbersToShipment($shipment, $trackingNumbers, $carrierCode, $carrierTitle);
        }
    }

    private function addTrackingNumbersToShipment(
        \Magento\Sales\Model\Order\Shipment $shipment,
        $trackingNumbers,
        $carrierCode,
        $carrierTitle
    ) {
        foreach ($trackingNumbers as $number) {
            if (is_array($number)) {
                $this->addTrackingNumbersToShipment($shipment, $number, $carrierCode, $carrierTitle);
            } else {
                $shipment->addTrack(
                    $this->trackFactory->create()
                        ->setNumber($number)
                        ->setCarrierCode($carrierCode)
                        ->setTitle($carrierTitle)
                );
            }
        }
    }
}
