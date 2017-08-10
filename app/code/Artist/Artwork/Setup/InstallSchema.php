<?php

namespace Artist\Artwork\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class InstallSchema implements InstallSchemaInterface {

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $setup->startSetup();

        $table = $setup->getConnection()->newTable(
                        $setup->getTable('art_table')
                )->addColumn(
                        'art_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Art Id'
                )->addColumn(
                        'art_user', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 255, [], 'Art User'
                )->addColumn(
                        'art_image', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [], 'Art Image'
                )->addColumn(
                        'art_title', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [], 'Art Title'
                )->addColumn(
                        'art_tag', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [], 'Art Tag'
                )->addColumn(
                        'art_cat', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 255, [], 'Art catalog'
                )->addColumn(
                        'art_who', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 255, ['default' => '1'], 'Art View work'
                )->addColumn(
                        'art_mature', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 255, ['default' => '1'], 'Mature content'
                )->addColumn(
                        'art_status', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 255, ['default' => '1'], 'Art status'
                )->setComment(
                'Art Table'
        );

        $setup->getConnection()->createTable($table);
        $table1 = $setup->getConnection()->newTable(
                                $setup->getTable('art_enable_prouct')
                        )->addColumn(
                                'id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id'
                        )->addColumn(
                                'artist_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 255, [], 'Art artist id  '
                        )->addColumn(
                                'art_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 255, [], 'Art Id'
                        )->addColumn(
                                'art_product', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 255, [], 'Art product id  '
                        )->addColumn(
                                'art_product_category', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 255, [], 'Art product category id'
                        )
                        ->addColumn(
                                'art_status', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 255, ['default' => '1'], 'Art status'
                        )->setComment(
                'Art products.'
        );

        $setup->getConnection()->createTable($table1);

        $table2 = $setup->getConnection()->newTable(
                        $setup->getTable('art_category_table')
                )->addColumn(
                        'id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Art Category Id'
                )->addColumn(
                        'art_category_title', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [], 'Art Category Title'
                )->addColumn(
                        'art_parent_category', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 255, [], 'Art Parent Category'
                )->addColumn(
                        'art_category_status', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 255, ['default' => '1'], 'Art Category status'
                )->setComment(
                'Art Category Table'
        );

        $setup->getConnection()->createTable($table2);

        $table3 = $setup->getConnection()->newTable(
                        $setup->getTable('artwork_category')
                )->addColumn(
                        'id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'id'
                )->addColumn(
                        'artwork_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 255, [], 'Artwork ID'
                )->addColumn(
                        'artwork_category_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 255, [], 'Artwork Category ID'
                )->setComment(
                'Artwork Category Table'
        );

        $setup->getConnection()->createTable($table3);
        
        $table4 = $setup->getConnection()->newTable(
                        $setup->getTable('artist_list')
                )->addColumn(
                        'artist_list_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Table primary key'
                )->addColumn(
                        'artist_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 255, [], 'Artist ID'
                )->addColumn(
                        'featured_status', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 4, ['default' => 0], 'Is Featured'
                )->setComment(
                'Artists List Table'
        );

        $setup->getConnection()->createTable($table4);

        $setup->endSetup();
    }

}
