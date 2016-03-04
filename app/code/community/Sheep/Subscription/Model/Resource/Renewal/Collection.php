<?php

/**
 * Class Sheep_Subscription_Model_Resource_Renewal_Collection
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Model_Resource_Renewal_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    /**
     * Specifies collections's model uri
     */
    protected function _construct()
    {
        $this->_init('sheep_subscription/renewal');
    }

    /**
     * Filters renewals by status
     *
     * @param $status
     * @return Sheep_Subscription_Model_Resource_Renewal_Collection
     */
    public function addStatusFilter($status)
    {
        return $this->addFieldToFilter('main_table.status', $status);
    }

    /**
     * Adds filter on renewal date
     *
     * @param string $time Datetime string using format Y-m-d H:i:s (in GMT)
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function addEarlierFilter($time)
    {
        return $this->addFieldToFilter('date', array(
            'to' => $time,
            'datetime' => true,
        ));
    }


    /**
     * Filters renewals in a specific period
     *
     * @param $startTime
     * @param $endTime
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function addBetweenFilter($startTime, $endTime)
    {
        return $this->addFieldToFilter('date', array(
            'from' => $startTime,
            'to' => $endTime,
            'datetime' => true,
        ));
    }


    /**
     * Adds subscription_id filter
     *
     * @param int $subscriptionId
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function addSubscriptionFilter($subscriptionId)
    {
        return $this->addFieldToFilter('main_table.subscription_id', $subscriptionId);
    }

    /**
     * Filters renewals associated to specified customer
     *
     * @param $customerId
     * @return $this
     */
    public function addCustomerFilter($customerId)
    {
        if (!array_key_exists('subscription', $this->_joinedTables)) {
            $this->addSubscriptionData(array('subscription.customer_id'));
        }

        $this->addFieldToFilter('subscription.customer_id', $customerId);

        return $this;
    }


    /**
     * Adds fields from related subscription
     *
     * @param string[] $subscriptionFields
     * @return $this
     */
    public function addSubscriptionData($subscriptionFields)
    {
        $this->join(
            array('subscription' => 'sheep_subscription/subscription'),
            'subscription.id = main_table.subscription_id',
            $subscriptionFields
        );

        return $this;
    }


    /**
     * Adds specified subscription quote fields. @see addSubscriptionData() needs to be called first
     *
     * @param string[] $quoteFields
     * @return $this
     */
    public function addSubscriptionQuoteData($quoteFields)
    {
        $this->join(
            array('q' => 'sales/quote'),
            'q.entity_id = subscription.quote_id',
            $quoteFields
        );

        return $this;
    }


    /**
     * Adds specified renewal order fields.
     *
     * @param array $orderFields
     * @return $this
     */
    public function addRenewalOrderData(array $orderFields)
    {
        $this->getSelect()->joinLeft(
            array('o' => $this->getTable('sales/order')),
            'o.entity_id = main_table.order_id',
            $orderFields
        );

        return $this;
    }

}

