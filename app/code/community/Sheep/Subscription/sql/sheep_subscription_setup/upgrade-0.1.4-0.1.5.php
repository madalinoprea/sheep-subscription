<?php

/** @var $this Mage_Sales_Model_Resource_Setup */
$installer = Mage::getResourceModel('sales/setup', 'core_setup');

$quoteAttributes = array(
    // Marks quotes that are used by subscription
    'pss_is_subscription' => array(
        'type'     => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
        'visible'  => false,
        'required' => false,
        'default' => Sheep_Subscription_Model_Subscription::QUOTE_IS_SUBSCRIPTION_NO,
    ),
    // Marks quotes that have subscriptions
    'pss_has_subscriptions' => array(
        'type'     => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
        'visible'  => false,
        'required' => false,
        'default' => Sheep_Subscription_Model_Subscription::QUOTE_HAS_SUBSCRIPTIONS_NO,
    ),
);

foreach ($quoteAttributes as $attributeCode => $attributeOptions) {
    $installer->addAttribute('quote', $attributeCode, $attributeOptions);
}

$installer->endSetup();
