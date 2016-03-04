<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/** @var Magento_Db_Adapter_Pdo_Mysql $connection */
$connection = $installer->getConnection();

$connection
    ->addColumn(
        $installer->getTable('sheep_subscription/type'),
        'discount',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'length'   => '12,4',
            'unsigned' => true,
            'nullable' => true,
            'default'  => null,
            'comment'  => 'Default discount percentage'
        ));

$installer->endSetup();
