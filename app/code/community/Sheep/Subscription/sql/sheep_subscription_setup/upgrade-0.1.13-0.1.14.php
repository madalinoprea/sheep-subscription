<?php
/**
 * @codeCoverageIgnore
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('sheep_subscription/product_subscription_type_price'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Product Subscription Type Price Id')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
    ), 'Product Id')
    ->addColumn('type_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
    ), 'Subscription Type Id')
    // Discount percent based on specified product price
    ->addColumn('discount', Varien_Db_Ddl_Table::TYPE_DECIMAL, array(12, 4), array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
    ), 'Subscription Type Price Discount')
    // Subscription price that replaces original product price if specified
    ->addColumn('price', Varien_Db_Ddl_Table::TYPE_DECIMAL, array(12, 4), array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
    ), 'Subscription Type Discounted Price')
    ->addForeignKey(
        $installer->getFkName('sheep_subscription/product_subscription_type_price', 'product_id', 'catalog/product', 'entity_id'),
        'product_id', $installer->getTable('catalog/product'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName('sheep_subscription/product_subscription_type_price', 'type_id', 'sheep_subscription/type', 'id'),
        'type_id', $installer->getTable('sheep_subscription/type'), 'id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Extra Data for Product - Subscription Type relationship');

$this->getConnection()->createTable($table);

$installer->endSetup();
