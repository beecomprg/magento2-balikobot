<?php


namespace Beecom\Balikobot\Cron;


use Beecom\Balikobot\Helper\Client;

class GenerateShipment
{
    /**
     * @var \Beecom\Balikobot\Model\Shipping\GenerateShipment
     */
    protected $model;

    protected $helper;

    /**
     * GenerateShipment constructor.
     * @param \Beecom\Balikobot\Model\Shipping\GenerateShipment $model
     */
    public function __construct(
        \Beecom\Balikobot\Model\Shipping\GenerateShipment $model,
        Client $helper
    )
    {
        $this->model = $model;
        $this->helper = $helper;
    }

    /**
     * @throws \Exception
     */
    public function execute()
    {
        if ($this->helper->isCronEnabled()) {
            $this->model->generateShipments();
        }
    }
}
