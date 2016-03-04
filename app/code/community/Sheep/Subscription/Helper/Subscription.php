<?php

/**
 * Class Sheep_Subscription_Helper_Subscription
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Helper_Subscription extends Mage_Core_Helper_Abstract
{

    /**
     * Returns subscription status options
     *
     * @return array
     */
    public function getStatusOptions()
    {
        return array(
            Sheep_Subscription_Model_Subscription::STATUS_ACTIVE => $this->__('Active'),
            Sheep_Subscription_Model_Subscription::STATUS_PAUSED => $this->__('Paused'),
            Sheep_Subscription_Model_Subscription::STATUS_CANCELLED => $this->__('Cancelled'),
            Sheep_Subscription_Model_Subscription::STATUS_EXPIRED => $this->__('Expired')
        );
    }

    /**
     * Returns customer subscriptions
     *
     * @param int $customerId
     * @return Sheep_Subscription_Model_Resource_Subscription_Collection
     */
    public function getCustomerSubscriptions($customerId)
    {
        /** @var Sheep_Subscription_Model_Resource_Subscription_Collection $customerSubscriptions */
        $customerSubscriptions = Mage::getModel('sheep_subscription/subscription')->getCollection();
        $customerSubscriptions->addCustomerFilter($customerId);
        return $customerSubscriptions;
    }


    /**
     * Returns active subscriptions belonging to specified customer
     *
     * @param int $customerId
     * @return Sheep_Subscription_Model_Resource_Subscription_Collection
     */
    public function getCustomerActiveSubscriptions($customerId)
    {
        return $this->getCustomerSubscriptions($customerId)->addStatusFilter(Sheep_Subscription_Model_Subscription::STATUS_ACTIVE);
    }


    /**
     * Returns all recurring products contained in active subscription belonging to specified customer
     *
     * @param $customerId
     * @return Mage_Sales_Model_Resource_Quote_Item_Collection
     */
    public function getCustomerRecurringProducts($customerId)
    {
        /** @var Mage_Sales_Model_Resource_Quote_Item_Collection $subscriptionProducts */
        $subscriptionProducts = Mage::getModel('sales/quote_item')->getCollection();
        $subscriptionProducts->join(array('q' => 'sales/quote'), 'quote_id = q.entity_id', array());
        $subscriptionProducts->join(array('s' => 'sheep_subscription/subscription'), 's.quote_id = q.entity_id', array());
        $subscriptionProducts->getSelect()->where('s.customer_id = ?', (int)$customerId);
        $subscriptionProducts->getSelect()->where('s.status = ?', Sheep_Subscription_Model_Subscription::STATUS_ACTIVE);

        return $subscriptionProducts;
    }
}
