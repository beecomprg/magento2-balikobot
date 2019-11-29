<?php
namespace Beecom\Balikobot\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if (version_compare($context->getVersion(), '0.1.1') < 0) {
            $this->upgradeVersionOneZeroTwo($installer);
        }

        if (version_compare($context->getVersion(), '0.1.2') < 0) {
            $this->upgradeVersionOneZeroThree($installer);
        }

        $installer->endSetup();
    }

    /**
     * @param SchemaSetupInterface $installer
     * @return void
     */
    private function upgradeVersionOneZeroTwo(SchemaSetupInterface $installer)
    {
        $eavTable1 = $installer->getTable('quote');
        $eavTable2 = $installer->getTable('sales_order');

        $columns = [
            'balikobot_type' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'Balikobot Type',
            ],
            'balikobot_branch' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'Balikobot Branch',
            ]
        ];

        $connection = $installer->getConnection();
        foreach ($columns as $name => $definition) {
            $connection->addColumn($eavTable1, $name, $definition);
            $connection->addColumn($eavTable2, $name, $definition);
        }
    }

    /**
     * @param SchemaSetupInterface $installer
     * @return void
     */
    private function upgradeVersionOneZeroThree(SchemaSetupInterface $installer)
    {
        $eavTable1 = $installer->getTable('quote');
        $eavTable2 = $installer->getTable('sales_order');

        $columns = [
            'balikobot_additional_info' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'Balikobot Additional Info',
            ]
        ];

        $connection = $installer->getConnection();
        foreach ($columns as $name => $definition) {
            $connection->addColumn($eavTable1, $name, $definition);
            $connection->addColumn($eavTable2, $name, $definition);
        }
    }
}
