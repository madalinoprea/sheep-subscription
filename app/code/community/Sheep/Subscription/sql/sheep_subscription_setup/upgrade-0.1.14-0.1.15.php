<?php
/**
 * @codeCoverageIgnore
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/** @var Magento_Db_Adapter_Pdo_Mysql $connection */
$connection = $installer->getConnection();

$connection->changeColumn(
    $installer->getTable('sheep_subscription/product_subscription_type_price'),
    'discount',
    'discount_percent',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'length'   => '12,4',
        'unsigned' => false,
        'nullable' => false,
        'default'  => 0,
        'comment'  => 'Subscription Type Price Discount Percent'
    ));

$connection->changeColumn(
    $installer->getTable('sheep_subscription/product_subscription_type_price'),
    'price',
    'discount',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'length'   => '12,4',
        'unsigned' => false,
        'nullable' => false,
        'default'  => 0,
        'comment'  => 'Subscription Type Price Discount'
    ));

$installer->endSetup();
