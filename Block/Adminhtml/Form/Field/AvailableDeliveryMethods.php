<?php


namespace Beecom\Balikobot\Block\Adminhtml\Form\Field;

use Beecom\Balikobot\Helper\Client;


class AvailableDeliveryMethods extends \Magento\Framework\View\Element\Html\Select
{

    protected $_client;

    protected $messageManager;

    protected $_carriers = [
        'zasilkovna' => 'Zasilkovna',
        'cp' => 'Česká pošta',
        'ppl' => 'PPL',
        'sp' => 'Slovenská pošta',
        'dpd' => 'DPD'
    ];


    public function __construct(
        Client $_client,
        \Magento\Framework\View\Element\Context $context,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        array $data = []
    )
    {
        $this->messageManager = $messageManager;
        $this->_client = $_client;
        parent::__construct($context, $data);
    }

    /**
     * Parse to html.
     *
     * @return mixed
     */
    public function _toHtml()
    {
        if ($this->_client->getClient() == false) {
            $this->addOption(0, 'Please fill out Balikobot credentials first and save');
            return parent::_toHtml();
        }
        if (!$this->getOptions()) {
            foreach ($this->_carriers as $carrierCode => $label) {
                try {
                    $services = $this->_client->getClient()->getServices($carrierCode);
                } catch (\Exception $exception) {
                    $services = null;
                    $this->messageManager->addErrorMessage(__('Please check Balikobot credentials - API user, key and Shop ID.'));
                }

                if ($services) {
                    foreach ($services as $code => $name) {
                        $this->addOption(sprintf("%s_%s", $carrierCode, $code), sprintf("[%s] - %s",$label, $name));
                    }
                } else {
                    $this->addOption($carrierCode, $label);
                }
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
