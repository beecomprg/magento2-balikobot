<?php

namespace Beecom\Balikobot\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Beecom\Balikobot\Helper\Client;
use Merinsky\Balikobot\Balikobot;

/**
 * An Abstract class for Indexer related commands.
 */
class Run extends Command
{

    const COMMAND_NAME = 'beecom:balikobot:run';

    /** @var \Magento\Framework\App\State **/
    private $state;

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $moduleReader;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    protected $helper;

    /**
     */
    public function __construct(
        \Magento\Framework\App\State $state,
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Client $helper,
        $data = []
    ) {
        $this->state = $state;
        $this->moduleReader = $moduleReader;
        $this->storeManager = $storeManager;
        $this->helper = $helper;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription('Command for running scheduled operation.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        $client = $this->helper->getClient();
        $response = $client->getServices('cp');
        var_dump($response);
    }

}
