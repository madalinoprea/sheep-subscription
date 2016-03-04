<?php

/**
 * Class Sheep_Subscription_Model_SalesRule_Condition_Product
 *
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2016, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Model_SalesRule_Condition_Product extends Sheep_Subscription_Model_SalesRule_Condition_Base
{
    const CONDITION_ATTRIBUTE_RECURRING_PRODUCT = 'pss_products';

    /**
     * Attributes related to a subscription product
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $attributes = array(self::CONDITION_ATTRIBUTE_RECURRING_PRODUCT => 'Subscription Product Skus');
        $this->setAttributeOption($attributes);

        return $this;
    }


    /**
     * We allow to enter a list of product skus
     *
     * @return string
     */
    public function getInputType()
    {
        return 'grid';
    }


    /**
     * Using a textbox
     *
     * @return string
     */
    public function getValueElementType()
    {
        return 'text';
    }


    /**
     * Checks of current customer has active subscriptions with at least of specified product skus
     *
     * @param Varien_Object $object
     * @return bool
     */
    public function validate(Varien_Object $object)
    {
        $customerId = $this->getCustomerId($object);

        if (!$customerId) {
            return false;
        }

        $recurringProducts = $this->getMatchingRecurringSkus($customerId, $this->getValueParsed());

        return $recurringProducts->getSize() > 0;
    }


    /**
     * Returns a collection of recurring products contained by all active subscription purchased by specified customer
     *
     * @param int $customerId
     * @param array $matchingSkus
     * @return Mage_Sales_Model_Resource_Quote_Item_Collection
     */
    public function getMatchingRecurringSkus($customerId, $matchingSkus)
    {
        /** @var Sheep_Subscription_Helper_Subscription $helper */
        $helper = Mage::helper('sheep_subscription/subscription');
        $recurringProducts = $helper->getCustomerRecurringProducts($customerId);

        // Join product table to use index on sku column (sales_flat_quote_item is only indexed by product_id)
        $recurringProducts->join(array('p' =>'catalog/product'), 'p.entity_id = product_id', array());
        $recurringProducts->getSelect()->where('p.sku IN (?)', $matchingSkus);

        return $recurringProducts;
    }
}
