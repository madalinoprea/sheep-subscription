<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/** @var Magento_Db_Adapter_Pdo_Mysql $connection */
$connection = $installer->getConnection();

$connection
    ->addColumn(
        $installer->getTable('sheep_subscription/renewal'),
        'failed_payments_count',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'unsigned' => true,
            'nullable' => false,
            'default' => 0,
            'comment'  => 'Number of failed payments'
        ));

$installer->endSetup();
