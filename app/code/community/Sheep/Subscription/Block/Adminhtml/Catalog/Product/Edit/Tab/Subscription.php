<?php

/**
 * Class Sheep_Subscription_Block_Adminhtml_Catalog_Product_Edit_Tab_Subscription
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Block_Adminhtml_Catalog_Product_Edit_Tab_Subscription extends
    Mage_Adminhtml_Block_Widget_Form
{

    /**
     * Returns current product
     *
     * @return Mage_Catalog_Model_Product|null
     */
    public function getProduct()
    {
        return Mage::registry('pss_current_product');
    }


    /**
     * Adds subscription configuration fieldset
     */
    public function addConfigurationFieldset()
    {
        if (!Mage::getSingleton('sheep_subscription/adminhtml_acl')->canViewProductSubscriptionConfiguration()) {
            return;
        }

        $currentProduct = $this->getProduct();

        /** @var Sheep_Subscription_Helper_Product $helper */
        $helper = Mage::helper('sheep_subscription/product');
        $form = $this->getForm();

        $fieldset = $form->addFieldset('subscription_form', array('legend' => $helper->__('Subscription Configuration')));
        $fieldset->addField('pss_is_subscription', 'select', array(
            'label'    => $helper->__('Is Subscription'),
            'name'     => 'pss_is_subscription',
            'required' => false,
            'values'   => $helper->getIsSubscriptionOptions(),
            'value'    => $currentProduct->getPssIsSubscription()
        ));

        $fieldset->addField('pss_subscription_type', 'multiselect', array(
            'label'    => $helper->__('Subscription Types'),
            'name'     => 'pss_subscription_type',
            'required' => false,
            'values'   => $helper->getSubscriptionTypeOptions(),
            'value'    => $helper->getProductSubscriptionTypeIdsValues($currentProduct)
        ));
    }


    /**
     * Adds subscription type price fieldset
     */
    public function addPriceFieldset()
    {
        if (!Mage::getSingleton('sheep_subscription/adminhtml_acl')->canViewProductSubscriptionPrices()) {
            return;
        }

        $product = $this->getProduct();
        $helper = Mage::helper('sheep_subscription/product');
        $form = $this->getForm();
        $fieldset = $form->addFieldset('subscription_prices_form', array('legend' => $helper->__('Subscription Prices')));

        if ($helper->isSubscriptionProduct($product)) {
            $typePrice = $fieldset->addField('pss_subscription_type_price', 'text', array(
                'name'  => 'pss_subscription_type_price',
                'value' => ''
            ));
            $typePrice->setRenderer($this->getLayout()->createBlock('sheep_subscription/adminhtml_catalog_product_edit_tab_subscription_price'));
        } else {
            $fieldset->addField('pss_subscription_price_note', 'note', array(
                'text' => $helper->__('Subscription prices can be configured after you save current product as subscription product.'),
            ));
        }
    }


    /**
     * Adds subscription form elements for related product subscription attributes
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $form->setFieldNameSuffix('product');
        $this->setForm($form);

        $this->addConfigurationFieldset();
        $this->addPriceFieldset();

        return parent::_prepareForm();
    }
}
