<?php

/**
 * Class Sheep_Subscription_Model_Resource_Payment_Collection
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Model_Resource_Payment_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    protected function _construct()
    {
        $this->_init('sheep_subscription/payment');
    }


    /**
     * Selects payment info that have their expiration date before specified date
     *
     * @param string $date Date string using format Y-m-d (in GMT)
     *
     * @return Sheep_Subscription_Model_Resource_Payment_Collection
     */
    public function addEarlierFilter($date)
    {
        return $this->addFieldToFilter('expiration_date', array(
            'to'       => $date,
            'datetime' => false,
        ));
    }


    /**
     * Adds fields from related subscription
     *
     * @param array $subscriptionFields
     * @return $this
     */
    public function addSubscriptionData(array $subscriptionFields)
    {
        $this->join(
            array('s' => 'sheep_subscription/subscription'),
            's.id = subscription_id',
            $subscriptionFields
        );

        return $this;
    }

}
