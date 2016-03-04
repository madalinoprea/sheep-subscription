<?php
/**
 * @codeCoverageIgnore
 */

$installer = Mage::getResourceModel('catalog/setup', 'core_setup');
$installer->startSetup();

$helper = Mage::helper('sheep_subscription/product');
$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'pss_is_subscription', array(
    'group'        => 'Subscription',
    'label'        => 'Is Subscription',
    'type'         => 'int',
    'input'        => 'text',
    'visible'      => false,
    'required'     => false,
    'sort_order'   => 10,
    'user_defined' => true,
    'default'      => Sheep_Subscription_Helper_Product::PRODUCT_PURCHASE_ONLY,
    'searchable'   => false,
    'filterable'   => false,
    'global'       => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'note'         => 'Products marked as One Time Purchase cannot be bought as subscriptions.'
));

$installer->endSetup();
