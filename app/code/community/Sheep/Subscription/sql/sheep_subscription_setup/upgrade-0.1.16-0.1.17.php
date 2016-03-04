<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/** @var Magento_Db_Adapter_Pdo_Mysql $connection */
$connection = $installer->getConnection();

$connection
    ->addColumn(
        $installer->getTable('sheep_subscription/subscription'),
        'canceled_at',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DATETIME,
            'nullable' => true,
            'after'    => 'updated_at',
            'comment'  => 'Last cancellation date'
        ));

$installer->endSetup();
