<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/** @var Magento_Db_Adapter_Pdo_Mysql $connection */
$connection = $installer->getConnection();

$connection
    ->addColumn(
        $installer->getTable('sheep_subscription/subscription'),
        'type_id',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'unsigned' => true,
            'nullable' => false,
            'comment'  => 'Subscription Type'
        ));

$connection->addColumn(
    $installer->getTable('sheep_subscription/subscription'),
    'type_info',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
        'default'  => '',
        'nullable' => false,
        'comment'  => 'Type Info when subscription was created'
    ));

$connection->addForeignKey(
    $installer->getFkName('sheep_subscription/subscription', 'type_id', 'sheep_subscription/type', 'id'),
    $installer->getTable('sheep_subscription/subscription'),
    'type_id',
    $installer->getTable('sheep_subscription/type'),
    'id'
);

$installer->endSetup();
