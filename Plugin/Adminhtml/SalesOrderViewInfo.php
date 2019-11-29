<?php
namespace Beecom\Balikobot\Plugin\Adminhtml;

use Bold\OrderComment\Model\Data\OrderComment;

class SalesOrderViewInfo
{
    /**
     * @param \Magento\Sales\Block\Adminhtml\Order\View\Info $subject
     * @param string $result
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterToHtml(
        \Magento\Sales\Block\Adminhtml\Order\View\Info $subject,
        $result
    ) {
        $balikobotBlock = $subject->getLayout()->getBlock('balikobot_delivery_details');
        if ($balikobotBlock !== false && $subject->getNameInLayout() == 'order_info') {
            $balikobotBlock->setBalikobotType($subject->getOrder()->getData('balikobot_type'));
            $balikobotBlock->setBalikobotBranch($subject->getOrder()->getData('balikobot_branch'));
            $balikobotBlock->setBalikobotAdditionalInfo($subject->getOrder()->getData('balikobot_additional_info'));

            $result = $result . $balikobotBlock->toHtml();
        }

        return $result;
    }
}
