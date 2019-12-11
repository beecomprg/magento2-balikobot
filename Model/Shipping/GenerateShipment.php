<?php


namespace Beecom\Balikobot\Model\Shipping;

use Magento\Sales\Model\Order\Shipment\Validation\QuantityValidator;
use Magento\Sales\Model\ValidatorResultInterface;
use Magento\Store\Model\ScopeInterface;

class GenerateShipment
{
    protected $orderRepository;
    protected $searchCriteriaBuilder;
    protected $sortBuilder;
    protected $converter;
    protected $labelGenerator;
    protected $request;
    protected $_objectManager;
    protected $_state;
    protected $_configLoader;
    protected $logger;
    protected $shipmentLoader;
    private $shipmentValidator;
    protected $registry;
    protected $scopeConfig;

    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\SortOrderBuilder $sortBuilder,
        \Magento\Sales\Model\Convert\Order $converter,
        LabelGenerator $labelGenerator,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $shipmentLoader,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Model\Order\Shipment\ShipmentValidatorInterface $shipmentValidator = null
    )
    {
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortBuilder = $sortBuilder;
        $this->converter = $converter;
        $this->labelGenerator = $labelGenerator;
        $this->request = $request;
        $this->_objectManager = $objectManager;
        $this->_state = $this->_objectManager->get('Magento\Framework\App\State');
        $this->_configLoader = $this->_objectManager->get('Magento\Framework\ObjectManager\ConfigLoaderInterface');
        $this->logger = $logger;
        $this->shipmentLoader = $shipmentLoader;
        $this->registry = $registry;
        $this->shipmentValidator = $shipmentValidator ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Sales\Model\Order\Shipment\ShipmentValidatorInterface::class);
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @throws \Exception
     */
    public function generateShipments()
    {
        $this->logger->info('Generating shipments');
        $this->logger->info('Logging as admin');

        $this->logInAsAdmin();
        $collection = $this->getOrderCollection();
        $this->logger->info('Running collection');

        /** @var \Magento\Sales\Api\Data\OrderInterface $order */
        foreach ($collection as $order) {
            if ($order->canShip()) {
                $this->logger->info('Creating shipment for order: ' . $order->getIncrementId());
                try {
                    $this->createShipment($order);
                } catch (\InvalidArgumentException $exception) {
                    $this->logger->error('Shipment for order: ' . $order->getIncrementId() . ' failed. Reason: ' . $exception->getMessage());
                } catch (\Exception $exception) {
                    $this->logger->error('Shipment for order: ' . $order->getIncrementId() . ' failed. Reason: ' . $exception->getMessage());
                }
            }
        }
        $this->logger->info('Generating shipments finished ');
    }

    /**
     * @return \Magento\Sales\Api\Data\OrderSearchResultInterface
     * @throws \Exception
     */
    protected function getOrderCollection()
    {
        $date = new \DateTime();
        $hours = $this->scopeConfig->getValue('balikobot/general/cron_order_older', ScopeInterface::SCOPE_STORE);
        $hourText = 'hours';
        if ($hours == 1) {
            $hourText = 'hour';
        }

        $customDate = $date->modify("-" . $hours . " " . $hourText)->format('Y-m-d H:i:s');

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('balikobot_type',null,'neq')
            ->addFilter('created_at', $customDate,'gteq')
            ->addSortOrder(
                $this->sortBuilder->setField('entity_id')
                    ->setDescendingDirection()->create()
            )
            ->setPageSize(100)
            ->setCurrentPage(1)
            ->create();

        return $this->orderRepository->getList($searchCriteria);
    }

    /**
     * @param $order
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     */
    protected function createShipment($order)
    {
        $data = [];
        $this->shipmentLoader->setOrderId($order->getEntityId());
        $this->shipmentLoader->setShipmentId(null);
        $this->shipmentLoader->setShipment($data);
        $this->shipmentLoader->setTracking(null);
        $shipment = $this->shipmentLoader->load();

        /** @var ValidatorResultInterface $validationResult */
        $validationResult = $this->shipmentValidator->validate($shipment, [QuantityValidator::class]);

        if ($validationResult->hasMessages()) {
            throw new \Exception($validationResult->getMessages());
        }

        $shipment->register();

        $this->request->setPostValue('packages', $this->createPackage($order));
        $this->logger->info('Creating label for shipment');
        $this->labelGenerator->create($shipment, $this->request);

        $this->logger->info('Saving shipment');
        $this->_saveShipment($shipment);
        $this->registry->unregister('current_shipment');
    }

    protected function createPackage($order)
    {
        $packages = [];
        $params = [];
        $params['container'] = '';
        $params['weight'] = '2';
        $params['weight_units'] = 'KILOGRAM';
        $params['dimension_units'] = 'CENTIMETER';
        $params['length'] = '';
        $params['width'] = '';
        $params['height'] = '';
        $params['content_type'] = '';
        $params['content_type_other'] = '';

        $packages[1]['params'] = $params;

        $items = [];
        $weight = 0;

        /** @var \Magento\Sales\Model\Order\Item $orderItem */
        foreach ($order->getAllItems() AS $orderItem) {
            $weight = $weight + $orderItem->getWeight();
            $items[$orderItem->getId()] = [
                'qty' => (string) $orderItem->getQtyToShip(),
                'price' => $orderItem->getPriceInclTax(),
                'name' => $orderItem->getName(),
                'product_id' => $orderItem->getProductId(),
                'order_item_id' => $orderItem->getId(),
                'weight' => (string) $orderItem->getWeight(),
                'customs_value' => $orderItem->getPriceInclTax()
            ];
        }

        if ($weight > 0) {
            $packages[1]['params']['weight'] = (string) $weight;
        }

        $packages[1]['items'] = $items;

        return $packages;
    }

    protected function logInAsAdmin()
    {
        $areaCode = 'adminhtml';
        $username = $this->scopeConfig->getValue('balikobot/general/cron_admin_user', ScopeInterface::SCOPE_STORE);

        $this->request->setPathInfo('/admin');

        $this->_objectManager->configure($this->_configLoader->load($areaCode));

        /** @var \Magento\User\Model\User $user */
        $user = $this->_objectManager->get('Magento\User\Model\User')->loadByUsername($username);

        /** @var \Magento\Backend\Model\Auth\Session $session */
        $session = $this->_objectManager->get('Magento\Backend\Model\Auth\Session');
        $session->setUser($user);
        $session->processLogin();
    }

    /**
     * @param $shipment
     * @return $this
     */
    protected function _saveShipment($shipment)
    {
        $shipment->getOrder()->setIsInProcess(true);
        $transaction = $this->_objectManager->create(
            \Magento\Framework\DB\Transaction::class
        );
        $transaction->addObject(
            $shipment
        )->addObject(
            $shipment->getOrder()
        )->save();

        return $this;
    }
}
