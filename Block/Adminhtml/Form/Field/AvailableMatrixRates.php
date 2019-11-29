<?php


namespace Beecom\Balikobot\Block\Adminhtml\Form\Field;


use Magento\Framework\View\Element\Template\Context;

class AvailableMatrixRates  extends \Magento\Framework\View\Element\Html\Select
{
    protected $collection;
    public function __construct(
        \Beecom\MatrixRate\Model\ResourceModel\Carrier\Matrixrate\CollectionFactory $collection,
        Context $context
    )
    {
            $this->collection = $collection;
            parent::__construct($context);
    }

    public function _toHtml()
    {
        if (!$this->getOptions()) {

            $matrixRates = $this->collection->create();

            foreach ($matrixRates as $matrixRate) {
                $this->addOption('matrixrate_'.$matrixRate->getRateId(), $matrixRate->getShippingMethod().' - '.$matrixRate->getPrice());
            }
        }

        return parent::_toHtml();
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

}