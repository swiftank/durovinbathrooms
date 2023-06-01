<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://magezon.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_TabsPro
 * @copyright Copyright (C) 2018 Magezon (https://magezon.com)
 */

namespace Magezon\TabsPro\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'mgz_tabspro_tab'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('mgz_tabspro_tab')
        )->addColumn(
            'tab_id',
            Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Tab ID'
        )->addColumn(
            'name',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Tab Name'
        )->addColumn(
            'custom_class',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Custom Class'
        )->addColumn(
            'priority',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Priority'
        )->addColumn(
            'position',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Position'
        )->addColumn(
            'tabs',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '2M',
            [],
            'Tabs'
        )->addColumn(
            'creation_time',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            'Tab Creation Time'
        )->addColumn(
            'update_time',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
            'Tab Modification Time'
        )->addColumn(
            'is_active',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '1'],
            'Is Tab Active'
        )->addColumn(
            'show_productpage',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '1'],
            'Show on Product Page'
        )->addColumn(
            'conditions_serialized',
            Table::TYPE_TEXT,
            '2M',
            [],
            'Conditions Serialized'
        )->addColumn(
            'actions_serialized',
            Table::TYPE_TEXT,
            '2M',
            [],
            'Actions Serialized'
        )->addColumn(
            'custom_template',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Custom Template'
        )->addColumn(
            'product_template',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Product Template'
        )->addIndex(
            $setup->getIdxName(
                $installer->getTable('mgz_tabspro_tab'),
                ['name', 'tabs'],
                AdapterInterface::INDEX_TYPE_FULLTEXT
            ),
            ['name', 'tabs'],
            ['type' => AdapterInterface::INDEX_TYPE_FULLTEXT]
        )->setComment(
            'TabsPro Tab Table'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'mgz_tabspro_tab_store'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('mgz_tabspro_tab_store')
        )->addColumn(
            'tab_id',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'primary' => true],
            'Tab Id'
        )->addColumn(
            'store_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Store ID'
        )->addIndex(
            $installer->getIdxName('mgz_tabspro_tab_store', ['store_id']),
            ['store_id']
        )->addForeignKey(
            $installer->getFkName('mgz_tabspro_tab_store', 'tab_id', 'mgz_tabspro_tab', 'tab_id'),
            'tab_id',
            $installer->getTable('mgz_tabspro_tab'),
            'tab_id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('mgz_tabspro_tab_store', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Tab Store'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'mgz_tabspro_tab_customergroup'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('mgz_tabspro_tab_customergroup')
        )->addColumn(
            'tab_id',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'primary' => true],
            'Tab ID'
        )->addColumn(
            'customer_group_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Customer Group ID'
        )->addIndex(
            $installer->getIdxName('mgz_tabspro_tab_customergroup', ['customer_group_id']),
            ['customer_group_id']
        )->addForeignKey(
            $installer->getFkName('mgz_tabspro_tab_customergroup', 'tab_id', 'mgz_tabspro_tab', 'tab_id'),
            'tab_id',
            $installer->getTable('mgz_tabspro_tab'),
            'tab_id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('mgz_tabspro_tab_customergroup', 'customer_group_id', 'customer_group', 'customer_group_id'),
            'customer_group_id',
            $installer->getTable('customer_group'),
            'customer_group_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Tab Custom Group'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'mgz_tabspro_tab_product'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('mgz_tabspro_tab_product')
        )->addColumn(
            'tab_id',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'primary' => true],
            'Tab ID'
        )->addColumn(
            'block_id',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false, 'primary' => true],
            'Block ID'
        )->addColumn(
            'store_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Store ID'
        )->addColumn(
            'type',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Position'
        )->addColumn(
            'product_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Product Id'
        )->addColumn(
            'position',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'Position'
        )->addIndex(
            $installer->getIdxName('mgz_tabspro_tab_product', ['product_id']),
            ['product_id']
        )->addIndex(
            $installer->getIdxName('mgz_tabspro_tab_product', ['store_id']),
            ['store_id']
        )->addForeignKey(
            $installer->getFkName('mgz_tabspro_tab_product', 'tab_id', 'mgz_tabspro_tab', 'tab_id'),
            'tab_id',
            $installer->getTable('mgz_tabspro_tab'),
            'tab_id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('mgz_tabspro_tab_product', 'product_id', 'catalog_product_entity', 'entity_id'),
            'product_id',
            $installer->getTable('catalog_product_entity'),
            'entity_id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('mgz_tabspro_tab_product', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Tab Product'
        );
        $installer->getConnection()->createTable($table);

    }
}
