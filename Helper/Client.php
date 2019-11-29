<?php
namespace Beecom\Balikobot\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Beecom\Balikobot\Model\Adapter\Client as BalikobotClient;

class Client extends AbstractHelper
{
    const PATH_CLIENT_ENABLED = 'balikobot/general/enabled';

    protected $logger;

    protected $scopeConfig;

    protected $frameworkHelper;

    protected $vatModel;

    protected $client;

    /**
     * Client constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param Context $context
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Context $context,
        \Psr\Log\LoggerInterface $logger
    )
    {
        parent::__construct($context);
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
    }

    public function isEnabled($store = null)
    {
        return $this->scopeConfig->getValue(self::PATH_CLIENT_ENABLED, ScopeInterface::SCOPE_STORE, $store);
    }

    public function isCronEnabled($store = null)
    {
        return $this->scopeConfig->getValue('balikobot/general/cron_enabled', ScopeInterface::SCOPE_STORE, $store);
    }

    public function getClient()
    {
        $apiUser = $this->getApiUser();
        if (!$apiUser) {
            return false;
        }
        if (!$this->client) {
            $this->client = new BalikobotClient($this->getApiUser(), $this->getApiKey(), (int) $this->getShopId());
        }

        return $this->client;
    }

    public function getServiceCode($code)
    {
        if (strpos($code, "_") !== false) {
            return explode("_", $code);
        }

        return $code;
    }

    protected function getApiUser()
    {
        return $this->scopeConfig->getValue('balikobot/general/api_user', ScopeInterface::SCOPE_STORE);
    }

    protected function getApiKey()
    {
        return $this->scopeConfig->getValue('balikobot/general/api_key', ScopeInterface::SCOPE_STORE);
    }

    protected function getShopId()
    {
        return $this->scopeConfig->getValue('balikobot/general/api_shop_id', ScopeInterface::SCOPE_STORE);
    }
}
