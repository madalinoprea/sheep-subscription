<?php

/**
 * Class Sheep_Subscription_Block_Adminhtml_Catalog_Product_Edit_Tab_Subscription_Price
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Block_Adminhtml_Catalog_Product_Edit_Tab_Subscription_Price
    extends Mage_Adminhtml_Block_Widget
    implements Varien_Data_Form_Element_Renderer_Interface

{
    protected $subscriptionTypes;


    protected function _construct()
    {
        $this->setTemplate('sheep_subscription/catalog/product/edit/tab/price/subscription.phtml');
        parent::_construct();
    }


    /**
     * Adds "Add Price" button to layout
     *
     * @return Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Price_Tier
     */
    protected function _prepareLayout()
    {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label' => Mage::helper('catalog')->__('Add Price'),
                'id'    => 'add_subscription_price'
            ));

        $this->setChild('add_button', $button);
        return parent::_prepareLayout();
    }


    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }


    /**
     * Retrieve 'Add Price' button HTML
     *
     * @return string
     */
    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }


    /**
     * Returns product that is currently edited
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        return Mage::registry('pss_current_product');
    }


    /**
     * Returns subscription types currently associated to edited product
     *
     * @return array
     */
    public function getSubscriptionTypes()
    {
        if ($this->subscriptionTypes === null) {
            $this->subscriptionTypes = array();
            $types = Mage::helper('sheep_subscription/product')->getProductSubscriptionTypes($this->getProduct());

            foreach ($types as $type) {
                $this->subscriptionTypes[$type->getId()] = $type->getTitle();
            }
        }

        return $this->subscriptionTypes;
    }


    /**
     * Returns currently subscription types associated to current product as json
     *
     * @return string
     */
    public function getSubscriptionTypesJson()
    {
        return Mage::helper('core')->jsonEncode($this->getSubscriptionTypes());
    }


    /**
     * Returns current product subscription type price values as json
     *
     * @return array
     */
    public function getValuesJson()
    {
        $values = array();

        /** @var Sheep_Subscription_Model_Resource_ProductTypePrice_Collection $typePrices */
        $typePrices = Mage::helper('sheep_subscription/product')->getProductSubscriptionTypePrices($this->getProduct());
        foreach ($typePrices as $typePrice) {
            $values[] = array(
                'type_id'  => $typePrice->getTypeId(),
                'discount'    => $typePrice->getDiscount(),
                'discount_percent' => $typePrice->getDiscountPercent()
            );
        }
        return Mage::helper('core')->jsonEncode($values);
    }

}
