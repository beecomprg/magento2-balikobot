<?php


namespace Beecom\Balikobot\Controller\Adminhtml\Shipment;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Beecom\Balikobot\Helper\Client;

class Labels extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $collectionFactory;
    /**
     * @var Filter
     */
    protected $filter;
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    protected $redirectUrl = 'sales/shipment/';

    protected $client;
    /**
     * Preparation constructor.
     * @param Context $context
     * @param Filter $filter
     * @param \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $orderCollectionFactory
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Context $context,
        Filter $filter,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        Client $client
    ) {
        parent::__construct($context);
        $this->collectionFactory = $orderCollectionFactory;
        $this->orderRepository = $orderRepository;
        $this->filter = $filter;
        $this->client = $client;
    }

    public function execute()
    {
        try {
            $client = $this->client->getClient();
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            //mass action
            $packages = [];
            /** @var \Magento\Sales\Model\ResourceModel\Order\Shipment $order */
            foreach ($collection->getItems() as $shipment) {
                $data = $shipment->getData();
                $order = $this->orderRepository->get($data['order_id']);
                $deliverer = $this->client->getServiceCode($order->getBalikobotType());
                if (is_array($deliverer)) {
                    $carrierCode = $deliverer[0];
                } else {
                    $carrierCode = $deliverer;
                }

                foreach ($data['packages'] as $package) {
                    if (isset($package['package_id'])) {
                        $packages[$carrierCode][] = $package['package_id'];
                    }
                }
            }

            $returnedLinks = 'No links returned!';

            if ($packages) {
                $returnedLinks = 'Returned links from Balikobot:<br />';
                foreach ($packages as $shipper => $packageIds) {
                    $link = $client->getLabels($shipper, $packageIds);
                    $returnedLinks .= '<a target="_blank" href="' . $link . '">' . $link . '</a>' . '<br />';
                }

            }

            $this->messageManager->addSuccess($returnedLinks);

            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('sales/shipment/');
            return $resultRedirect;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath($this->redirectUrl);
        }
    }
}
