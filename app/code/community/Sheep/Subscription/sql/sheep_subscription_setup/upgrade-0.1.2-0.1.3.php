<?php

$installer = Mage::getResourceModel('catalog/setup', 'core_setup');
$installer->startSetup();

$helper = Mage::helper('sheep_subscription/product');
$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'pss_subscription_type', array(
    'group'        => 'Subscription',
    'label'        => 'Subscription Types',
    'type'         => 'varchar',
    'input'        => 'text',
    'visible'      => false,
    'required'     => false,
    'sort_order'   => 20,
    'user_defined' => true,
    'default'      => Sheep_Subscription_Helper_Product::PRODUCT_SUBSCRIPTION_TYPES_NONE,
    'searchable'   => false,
    'filterable'   => false,
    'global'       => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'note'         => 'How product types are associated to a product.'
));

$installer->endSetup();
