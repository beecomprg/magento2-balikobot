<?php

namespace Beecom\Balikobot\Model\Resolver\DataProvider;

use Beecom\Balikobot\Helper\Client;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Branch
{
    protected $balikobot;
    protected $scopeConfig;
    protected $cache;
    protected $serializer;

    public function __construct(
        Client $client,
        ScopeConfigInterface $scopeConfig,
        CacheInterface $cache,
        SerializerInterface $serializer
    ) {
        $this->balikobot = $client;
        $this->scopeConfig = $scopeConfig;
        $this->cache = $cache;
        $this->serializer = $serializer;
    }

    public function getBranches($args)
    {
        $deliverer = $this->balikobot->getServiceCode($args['balikobot_type']);
        $shippingMethod = $args['shipping_method'];

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
        $cacheIdentifier = $shippingMethod . '-' . $carrierCode . '-' . $countriesRestriction;

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

        $returnBranches = [];
        foreach ($allowedBranches as $branch) {
            $returnBranches[] = [
                'value' => $branch['id'],
                'label' => $branch['name']
            ];
        }

        return ['items' => $returnBranches];
    }
}
