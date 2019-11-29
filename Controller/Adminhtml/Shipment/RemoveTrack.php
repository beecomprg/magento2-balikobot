<?php


namespace Beecom\Balikobot\Controller\Adminhtml\Shipment;

use Beecom\Balikobot\Helper\Client as Helper;
use Magento\Backend\App\Action;
use Magento\Framework\App\Config\ScopeConfigInterface;

class RemoveTrack extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::shipment';

    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader
     */
    protected $shipmentLoader;

    protected $client;

    protected $orderRepository;

    protected $scopeConfig;

    protected $helper;

    /**
     * @param Action\Context $context
     * @param \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $shipmentLoader
     */
    public function __construct(
        Action\Context $context,
        \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $shipmentLoader,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        ScopeConfigInterface $scopeConfig,
        Helper $helper
    )
    {
        $this->shipmentLoader = $shipmentLoader;
        $this->orderRepository = $orderRepository;
        $this->scopeConfig = $scopeConfig;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * Remove tracking number from shipment
     *
     * @return void
     */
    public function execute()
    {
        $trackId = $this->getRequest()->getParam('track_id');
        /** @var \Magento\Sales\Model\Order\Shipment\Track $track */
        $track = $this->_objectManager->create(\Magento\Sales\Model\Order\Shipment\Track::class)->load($trackId);
        if ($track->getId()) {
            try {
                $this->shipmentLoader->setOrderId($this->getRequest()->getParam('order_id'));
                $this->shipmentLoader->setShipmentId($this->getRequest()->getParam('shipment_id'));
                $this->shipmentLoader->setShipment($this->getRequest()->getParam('shipment'));
                $this->shipmentLoader->setTracking($this->getRequest()->getParam('tracking'));
                $shipment = $this->shipmentLoader->load();
                if ($shipment) {
                    $orderId = $track->getOrderId();

                    $order = $this->orderRepository->get($orderId);
                    if ($order->getBalikobotType()) {
                        $matrixRateMapping = json_decode($this->scopeConfig->getValue('balikobot/general/mapping_matrix_rates'));
                        $mappingDecoded = [];
                        foreach ($matrixRateMapping as $item) {
                            $mappingDecoded['matrixrate_' . $item->matrixrate] = $item->balikobot;
                        }
                        $balikobotMethod = $mappingDecoded[$order->getShippingMethod()];
                        $deliverer = $this->helper->getServiceCode($balikobotMethod);
                        if (is_array($deliverer)) {
                            $carrierCode = $deliverer[0];
                        } else {
                            $carrierCode = $deliverer;
                        }

                        $shipmentPackages = $shipment->getPackages();
                        $packageId = $shipmentPackages[1]['package_id'];
                        $client = $this->helper->getClient();
                        $client->dropPackage($carrierCode, $packageId);
                    }

                    $track->delete();

                    $this->_view->loadLayout();
                    $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Shipments'));
                    $response = $this->_view->getLayout()->getBlock('shipment_tracking')->toHtml();
                } else {
                    $response = [
                        'error' => true,
                        'message' => __('We can\'t initialize shipment for delete tracking number.'),
                    ];
                }
            } catch (\UnexpectedValueException $e) {
                $response = ['error' => true, 'message' => $e->getMessage()];
            } catch (\Exception $e) {
                $response = ['error' => true, 'message' => $e->getMessage()];
            }
        } else {
            $response = [
                'error' => true,
                'message' => __('We can\'t load track with retrieving identifier right now.')
            ];
        }
        if (is_array($response)) {
            $response = $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode($response);
            $this->getResponse()->representJson($response);
        } else {
            $this->getResponse()->setBody($response);
        }
    }
}