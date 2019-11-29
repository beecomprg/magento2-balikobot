<?php


namespace Beecom\Balikobot\Block\Adminhtml\Form\Field;


class AvailableCountries extends \Magento\Framework\View\Element\Html\Select
{
    public function _toHtml()
    {
        if (!$this->getOptions()) {

            $countries = [
                ['value' => 'CZ', 'label' => 'Czech republic'],
                ['value' => 'SK', 'label' => 'Slovakia'],
                ['value' => 'europe', 'label' => 'Europe without CZ and SK']
            ];

            foreach ($countries as $country) {
                $this->addOption($country['value'], $country['label']);
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
