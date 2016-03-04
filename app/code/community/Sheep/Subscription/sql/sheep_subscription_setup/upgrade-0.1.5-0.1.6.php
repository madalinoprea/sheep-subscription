<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('sheep_subscription/renewal'))
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
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable' => false,
    ), 'Renewal Status')
    ->addColumn('date', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
        'nullable' => false,
    ), 'Renewal Date')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
        'nullable' => false,
    ), 'Renewal creation time')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
        'nullable' => false,
    ), 'Renewal last update time')
    ->addColumn('last_message', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        'nullable' => false,
        'default'  => ''
    ), 'Renewal last message')
    ->addForeignKey(
        $installer->getFkName('sheep_subscription/renewal', 'subscription_id', 'sheep_subscription/subscription', 'id'),
        'subscription_id', $installer->getTable('sheep_subscription/subscription'), 'id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addIndex(
        $installer->getIdxName('sheep_subscription/renewal', 'status', Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX),
        'status',
        Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX)
    ->setComment('Subscription Renewals');

$this->getConnection()->createTable($table);

$installer->endSetup();
