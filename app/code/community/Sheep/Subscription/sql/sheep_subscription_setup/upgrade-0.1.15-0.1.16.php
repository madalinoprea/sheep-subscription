<?php
/**
 * @codeCoverageIgnore
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/** @var Magento_Db_Adapter_Pdo_Mysql $connection */
$connection = $installer->getConnection();

$connection->dropColumn(
    $installer->getTable('sheep_subscription/subscription'),
    'product_id'
);

$installer->endSetup();
