<?php

namespace Beecom\Balikobot\Block\Adminhtml;

use Bold\OrderComment\Model\Data\OrderComment;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\Sales\Model\Order;

class Balikobot extends \Magento\Framework\View\Element\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    public function __construct(
        TemplateContext $context,
        Registry $registry,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->_isScopePrivate = true;
        $this->_template = 'order/view/balikobot.phtml';
        parent::__construct($context, $data);
    }

    public function getOrder() : Order
    {
        return $this->coreRegistry->registry('current_order');
    }

    public function getBalikobotType(): string
    {
        return trim($this->getOrder()->getData('balikobot_type'));
    }

    public function getBalikobotBranch(): string
    {
        return trim($this->getOrder()->getData('balikobot_branch'));
    }

    public function getBalikobotAdditionalInfo(): string
    {
        return trim($this->getOrder()->getData('balikobot_additional_info'));
    }
}
