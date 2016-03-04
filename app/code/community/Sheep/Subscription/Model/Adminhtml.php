<?php

/**
 * Class Sheep_Subscription_Model_Adminhtml
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Model_Adminhtml
{
    /**
     * @return Sheep_Subscription_Model_Adminhtml_Acl
     */
    public function getAdminAcl()
    {
        return Mage::getSingleton('sheep_subscription/adminhtml_acl');
    }


    /**
     * Adds subscription tab based on rendered block
     *
     * @param Mage_Core_Block_Abstract $block
     */
    public function addSubscriptionTabs(Mage_Core_Block_Abstract $block)
    {
        if ($block->getId() === 'product_info_tabs') {
            $this->addSubscriptionTabOnProductInfoTabs($block);
        } else if ($block->getId() === 'customer_info_tabs') {
            $this->addSubscriptionTabOnCustomerTabs($block);
        }
    }


    /**
     * Adds subscription configuration tab on products can be sell as subscriptions
     *
     * @param Mage_Adminhtml_Block_Catalog_Product_Edit_Tabs $block
     * @return $this
     */
    public function addSubscriptionTabOnProductInfoTabs(Mage_Adminhtml_Block_Catalog_Product_Edit_Tabs $block)
    {
        // Show subscription tab only if we have permissions
        if (!$this->getAdminAcl()->canShowProductSubscriptionTab()) {
            return;
        }

        /** @var Sheep_Subscription_Helper_Product $helper */
        $helper = Mage::helper('sheep_subscription/product');
        /** @var Mage_Catalog_Model_Product $product */
        $product = $block->getProduct();

        if ($helper->isEnabledForProductType($product->getTypeId())) {
            $block->addTabAfter('sheep_subscription', array(
                'label' => $helper->__('Subscription'),
                'url'   => $block->getUrl('adminhtml/subscription/configurationProductTab', array('product_id' => $product->getId())),
                'class' => 'ajax',
            ), 'inventory');
        }
    }


    /**
     * Adds subscription tab that lists subscription associated to customer
     *
     * @param Mage_Adminhtml_Block_Customer_Edit_Tabs $block
     */
    public function addSubscriptionTabOnCustomerTabs(Mage_Adminhtml_Block_Customer_Edit_Tabs $block)
    {
        if (!$this->getAdminAcl()->canShowCustomerSubscriptionTab()) {
            return;
        }

        /** @var Sheep_Subscription_Helper_Product $helper */
        $helper = Mage::helper('sheep_subscription/product');

        $customer = Mage::registry('current_customer');

        $block->addTabAfter('sheep_subscription', array(
            'label'  => $helper->__('Subscriptions'),
            'url'    => $block->getUrl('adminhtml/subscription/customerTab', array('customer_id' => $customer->getId())),
            'class'  => 'ajax',
            'active' => false,
        ), 'orders');
        $block->assign('tabs', $block->getTabsIds());

        $block->setActiveTab(null);
    }

}
