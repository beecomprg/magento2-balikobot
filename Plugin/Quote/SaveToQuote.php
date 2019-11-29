<?php
namespace Beecom\Balikobot\Plugin\Quote;

use Magento\Quote\Model\QuoteRepository;

class SaveToQuote
{

    /**
     * @var QuoteRepository
     */
    protected $quoteRepository;
    /**
     * SaveToQuote constructor.
     * @param QuoteRepository $quoteRepository
     */
    public function __construct(
        QuoteRepository $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
    }
    /**
     * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
     * @param $cartId
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     */
    public function beforeSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
        $quote = $this->quoteRepository->getActive($cartId);
        if (!$extAttributes = $addressInformation->getExtensionAttributes()) {
            $quote->setBalikobotType(null);
            $quote->setBalikobotBranch(null);
            $quote->setBalikobotAdditionalInfo(null);

            return;
        }

        $quote->setBalikobotType($extAttributes->getBalikobotType());
        $quote->setBalikobotBranch($extAttributes->getBalikobotBranch());
        $quote->setBalikobotAdditionalInfo($extAttributes->getBalikobotAdditionalInfo());
    }
}
