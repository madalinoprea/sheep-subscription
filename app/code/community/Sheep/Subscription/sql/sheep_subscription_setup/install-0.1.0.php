<?php
/**
 * @codeCoverageIgnore
 */
 
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('sheep_subscription/type'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Type Id')
    ->addColumn('title', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => false,
    ), 'Title')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
        'nullable'  => false,
        'default'   => Sheep_Subscription_Model_Type::STATUS_ENABLED,
    ), 'Status')
    ->addColumn('period_count', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => 1,
    ), 'Period Count')
    ->addColumn('period_unit', Varien_Db_Ddl_Table::TYPE_VARCHAR, 100, array(
        'nullable'  => false,
        'default'   => 'day',
    ), 'Period Unit')
    ->addColumn('is_infinite', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
        'nullable'  => false,
        'default'   => 1,
    ), 'Is Infinite')
    ->addColumn('occurrences', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => 0,
    ), 'Number of occurrences if is finite')
    ->addColumn('has_trial', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
        'nullable'  => false,
        'default'   => 1,
    ), 'Count')
    ->addColumn('trial_occurrences', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => 0,
    ), 'Trial Occurrences')
    ->addColumn('initial_fee', Varien_Db_Ddl_Table::TYPE_DECIMAL, array(12, 4), array(
        'nullable'  => true,
        'default'   => null,
    ), 'Initial Fee')
    ->addIndex($this->getIdxName('sheep_subscription/type', array('status')), array('status'))
;

$this->getConnection()->createTable($table);

$installer->endSetup();
