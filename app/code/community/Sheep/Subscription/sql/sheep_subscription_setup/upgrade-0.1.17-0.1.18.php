<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/** @var Magento_Db_Adapter_Pdo_Mysql $connection */
$connection = $installer->getConnection();

$connection->addColumn(
    $installer->getTable('sheep_subscription/payment'),
    'expiration_date',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_DATE,
        'nullable' => true,
        'after'    => 'subscription_id',
        'comment'  => 'Payment info expiration date'
    ));


$connection->addIndex(
    $installer->getTable('sheep_subscription/payment'),
    $installer->getIdxName('sheep_subscription/payment', 'expiration_date'),
    'expiration_date',
    Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
);

$installer->endSetup();
