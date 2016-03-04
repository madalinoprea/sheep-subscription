<?php

/**
 * Class Sheep_Subscription_Block_Subscription_Info
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
abstract class Sheep_Subscription_Block_Subscription_Abstract extends Mage_Core_Block_Template
{
    /** @var Mage_Customer_Model_Customer $customer */
    protected $customer;

    /** @var Sheep_Subscription_Model_Subscription */
    protected $subscription;

    /**
     * Returns current subscription
     * @return Sheep_Subscription_Model_Subscription
     */
    public function getSubscription()
    {
        if ($this->subscription === null) {
            $this->subscription = Mage::registry('pss_subscription');
        }

        return $this->subscription;
    }


    /**
     * Returns current customer
     *
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        if ($this->customer === null) {
            $this->customer = Mage::getSingleton('customer/session')->getCustomer();
        }

        return $this->customer;
    }
}
