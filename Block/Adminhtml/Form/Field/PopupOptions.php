<?php


namespace Beecom\Balikobot\Block\Adminhtml\Form\Field;

class PopupOptions extends \Magento\Framework\View\Element\Html\Select
{

    public function __construct(\Magento\Framework\View\Element\Context $context, array $data = [])
    {
        parent::__construct($context, $data);
    }

    /**
     * Parse to html.
     *
     * @return mixed
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {

            $popups = [
                [
                    'value' => 0,
                    'label' => 'No'
                ],
                [
                    'value' => 1,
                    'label' => 'Yes'
                ]
            ];

            foreach ($popups as $popup) {
                $this->addOption($popup['value'], $popup['label']);
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
