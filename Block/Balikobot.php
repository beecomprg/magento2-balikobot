<?php


namespace Beecom\Balikobot\Block;

use \Magento\Framework\View\Element\Template;
use \Magento\Framework\View\Element\Template\Context;

class Balikobot extends Template
{
    public function __construct(
        Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    public function getBranchesData()
    {
        return $this->getBranches();
    }
}
