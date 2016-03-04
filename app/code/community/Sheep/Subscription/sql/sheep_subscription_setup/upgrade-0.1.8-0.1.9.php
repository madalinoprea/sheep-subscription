<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/** @var Magento_Db_Adapter_Pdo_Mysql $connection */
$connection = $installer->getConnection();

$connection
    ->addColumn(
        $installer->getTable('sheep_subscription/subscription'),
        'original_order_id',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'unsigned' => true,
            'nullable' => true,
            'comment'  => 'Initial order id that created this subscription'
        ));

$connection->addForeignKey(
    $installer->getFkName('sheep_subscription/subscription', 'original_order_id', 'sales/order', 'entity_id'),
    $installer->getTable('sheep_subscription/subscription'),
    'original_order_id',
    $installer->getTable('sales/order'),
    'entity_id'
);

$installer->endSetup();
