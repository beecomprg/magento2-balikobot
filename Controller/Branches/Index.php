<?php
namespace Beecom\Balikobot\Controller\Branches;

use Beecom\Balikobot\Helper\Client;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Serialize\SerializerInterface;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var Client
     */
    protected $balikobot;

    /**
     * @var Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    protected $cache;

    protected $serializer;

    /**
     * Index constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param PageFactory $resultPageFactory
     * @param Client $client
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        PageFactory $resultPageFactory,
        Client $client,
        \Magento\Framework\App\CacheInterface $cache,
        SerializerInterface $serializer,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->balikobot = $client;
        $this->resultPageFactory = $resultPageFactory;
        $this->scopeConfig = $scopeConfig;
        $this->cache = $cache;
        $this->serializer = $serializer;
        return parent::__construct($context);
    }

    /**
     * Ajax action for inline translation
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $deliverer = $this->balikobot->getServiceCode($this->getRequest()->getParam('deliverer'));
        $shippingMethod = $this->getRequest()->getParam('shippingMethod');

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $mappingCountries = $this->scopeConfig->getValue('balikobot/general/mapping_countries', $storeScope);
        $mappingCountries = \GuzzleHttp\json_decode($mappingCountries);

        $countriesRestrictions = [];
        foreach ($mappingCountries as $country) {
            $countriesRestrictions[$country->matrixrate] = $country->country;
        }

        if (is_array($deliverer)) {
            $carrierCode = $deliverer[0];
            $serviceType = $deliverer[1];
        } else {
            $carrierCode = $deliverer;
            $serviceType = null;
        }

        $countriesRestriction = $countriesRestrictions[$shippingMethod];
        $cacheIdentifier = $shippingMethod.'-'.$carrierCode.'-'.$countriesRestriction;

        $allowedBranches = $this->cache->load($cacheIdentifier);
        if (!$allowedBranches) {
            $branches = $this->balikobot->getClient()->getBranches($carrierCode, $serviceType);

            $allowedBranches = [];
            foreach ($branches as $branch) {
                if ($countriesRestriction == 'europe') {
                    $restrictedCountries = ['CZ', 'SK'];
                    if (!in_array($branch['country'], $restrictedCountries)) {
                        $allowedBranches[] = $branch;
                    }
                    continue;
                }
                if ($branch['country'] == $countriesRestriction) {
                    $allowedBranches[] = $branch;
                }
            }

            $data = $this->serializer->serialize($allowedBranches);
            $this->cache->save($data, $cacheIdentifier);
        } else {
            $allowedBranches = $this->serializer->unserialize($allowedBranches);
        }

        $html = '<div class="pa-4"><select id="balikobot_select" name="branch_id" data-mage-init=\'{"select2":{}}\'>';

        foreach ($allowedBranches as $branch) {
            $html .= '<option value="' . $branch['id'] . '">' . $branch['name'] . '</option>';
        }
        $html .= '</select></div>';

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();

        $this->_actionFlag->set('', self::FLAG_NO_POST_DISPATCH, true);
        return $resultJson->setData(['html' => $html]);
    }
}
