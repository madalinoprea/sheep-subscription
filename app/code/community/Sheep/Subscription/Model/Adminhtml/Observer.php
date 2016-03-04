<?php

/**
 * Class Sheep_Subscription_Model_Adminhtml_Observer defines observers for events raised in Admin
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2016, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Model_Adminhtml_Observer
{

    /**
     * @return Sheep_Subscription_Model_Adminhtml_Acl
     */
    public function getAcl()
    {
        return Mage::getSingleton('sheep_subscription/adminhtml_acl');
    }


    /**
     * Listens to core_block_abstract_prepare_layout_after in adminhtml area and adds subscription tabs on
     * different pages
     *
     * @param Varien_Event_Observer $observer
     */
    public function addSubscriptionTabs(Varien_Event_Observer $observer)
    {
        /** @var Mage_Adminhtml_Block_Template $block */
        $block = $observer->getBlock();
        /** @var Sheep_Subscription_Model_Adminhtml $model */
        $model = Mage::getModel('sheep_subscription/adminhtml');
        $model->addSubscriptionTabs($block);
    }


    /**
     * Listens to catalog_product_prepare_save and adds request info on product model that needs to be processed
     * before save by @see Sheep_Subscription_Model_Observer::saveProductSubscriptionTypes
     *
     * @param Varien_Event_Observer $observer
     */
    public function prepareProductSave(Varien_Event_Observer $observer)
    {
        $request = $observer->getEvent()->getRequest();
        $product = $observer->getEvent()->getProduct();
        $productData = $request->getPost('product');
        $acl = $this->getAcl();

        if (array_key_exists('pss_subscription_type', $productData) && $acl->canEditProductSubscriptionConfiguration()) {
            $product->setPssSubscriptionData($productData['pss_subscription_type']);
        }

        if (array_key_exists('pss_subscription_type_price', $productData) && $acl->canEditProductSubscriptionPrices()) {
            $product->setPssSubscriptionTypePriceData($productData['pss_subscription_type_price']);
        }
    }


    /**
     * Listens catalog_product_save_before and updates product subscription types information.
     *
     * @param Varien_Event_Observer $observer
     */
    public function saveProductSubscriptionTypes(Varien_Event_Observer $observer)
    {
        $product = $observer->getProduct();
        if (!$product || !$product->getId()) {
            return;
        }

        if ($product->getPssSubscriptionData()) {
            Mage::helper('sheep_subscription/product')->setProductSubscriptionTypes($product, $product->getPssSubscriptionData());
        }


        if ($product->getPssSubscriptionTypePriceData()) {
            Mage::helper('sheep_subscription/product')->setProductSubscriptionTypePrices($product, $product->getPssSubscriptionTypePriceData());
        }
    }

}
