<?php

/**
 * Class Sheep_Subscription_Block_Cart_Item_Info
 *
 * @category Sheep
 * @package Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Block_Cart_Item_Info extends Mage_Core_Block_Template
{
    /** @var Mage_Sales_Model_Quote_Item $_item */
    protected $_item;
    protected $_isRecurringProduct;
    /** @var Sheep_Subscription_Model_Type */
    protected $_type;

    protected $_discount;

    /**
     * Initialize block fields
     */
    public function init()
    {
        $parentBlock = $this->getLayout()->getBlock('additional.product.info');
        $this->_item = $parentBlock ? $parentBlock->getItem() : null;

        if ($this->_item) {
            $helper = Mage::helper('sheep_subscription/quote');
            $this->_isRecurringProduct = $helper->isSubscriptionQuoteItem($this->_item);
            $this->_type = $helper->getSubscriptionType($this->_item);

            // compute the discount
            if ($this->_type) {
                $types = Mage::getResourceModel('sheep_subscription/type_collection')->addFieldToFilter('id', $this->_type->getId());

                $service = Mage::getModel('sheep_subscription/service');
                $service->addProductPriceToType($this->_item->getProduct(), $types);

                $type = $types->getItemById($this->_type->getId());
                if ($type->getPrice()) {
                    $this->_discount = $this->getDiscountDescription($type->getPrice(), $type->getPriceType() == 'percent');
                }
            }
        }
    }


    /**
     * Computes discount description
     *
     * @param $amount
     * @param $isPercentage
     * @return string
     */
    public function getDiscountDescription($amount, $isPercentage)
    {
        if ($isPercentage) {
            return (-1 * $amount) . '%';
        } else {
            return $this->helper('checkout')->formatPrice(-1 * $amount * $this->_item->getQty());
        }
    }


    /**
     * Returns quote item set on additional.product.info block
     *
     * @return Mage_Sales_Model_Quote_Item
     */
    public function getItem()
    {
        return $this->_item;
    }


    /**
     * @return mixed
     */
    public function getSubscriptionDiscount()
    {
        return $this->_discount;
    }


    public function getNextRenewal()
    {
        if ($this->_type) {
            $now = Mage::getSingleton('core/date')->gmtDate();
            return $this->_type->getNextRenewalDate($now);
        }
    }


    protected function _toHtml()
    {
        $this->init();

        if (!$this->_isRecurringProduct) {
            return '';
        }

        return parent::_toHtml();
    }

}
