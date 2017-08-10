<?php

namespace Image\Settings\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class InstallSchema implements InstallSchemaInterface {

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $setup->startSetup();

        $table = $setup->getConnection()->newTable(
                        $setup->getTable('image_settings')
                )->addColumn(
                        'id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id'
                )->addColumn(
                        'artist_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 255, [], 'Artist Id'
                )->addColumn(
                        'attr_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 255, [], 'Attribute Id'
                )->addColumn(
                        'watermark_image', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [], 'Watermark Image'
                )->setComment(
                'Customer Image Settings'
        );

        $setup->getConnection()->createTable($table);

        $table_wm = $setup->getConnection()->newTable(
                        $setup->getTable('image_watermark')
                )->addColumn(
                        'id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id'
                )->addColumn(
                        'artist_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 255, [], 'Artist Id'
                )->addColumn(
                        'product_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 255, [], 'Product Id'
                )->addColumn(
                        'attr_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 255, [], 'Attribute Id'
                )->addColumn(
                        'customer_watermark', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [], 'Customer Watermark Image'
                )->setComment(
                'Customer Watermark Image'
        );

        $setup->getConnection()->createTable($table_wm);

        $setup->endSetup();
    }

}

?>
