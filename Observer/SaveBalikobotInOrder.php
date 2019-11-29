<?php
namespace Beecom\Balikobot\Observer;

class SaveBalikobotInOrder  implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $quote = $observer->getEvent()->getQuote();
        $order->setData("balikobot_type",$quote->getBalikobotType());
        $order->setData("balikobot_branch",$quote->getBalikobotBranch());
        $order->setData("balikobot_additional_info",$quote->getBalikobotAdditionalInfo());
        return $this;
    }
}
