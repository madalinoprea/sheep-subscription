<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('sheep_subscription/payment'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary'  => true,
    ), 'Renewal Id')
    ->addColumn('subscription_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
    ), 'Subscription Id')
    ->addColumn('info', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        'nullable' => false,
        'default' => '',
    ), 'Additional Payment Info')
    ->addForeignKey(
        $installer->getFkName('sheep_subscription/payment', 'subscription_id', 'sheep_subscription/subscription', 'id'),
        'subscription_id', $installer->getTable('sheep_subscription/subscription'), 'id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Subscription Payment Information');

$this->getConnection()->createTable($table);

$installer->endSetup();
