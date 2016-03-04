<?php

/**
 * Class Sheep_Subscription_Block_Subscription_Edit_ShippingMethod
 *
 * @category Sheep
 * @package Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Block_Subscription_Edit_ShippingMethod extends Mage_Checkout_Block_Onepage_Shipping_Method_Available
{
    protected $subscription;


    /**
     * Returns current subscription
     *
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
     * Returns subscription url
     *
     * @return string
     */
    public function getBackUrl()
    {
        return Mage::helper('sheep_subscription')->getSubscriptionUrl($this->getSubscription()->getId());
    }


    /**
     * Returns url where data needs to be posted to
     *
     * @return string
     */
    public function getSaveShippingMethodUrl()
    {
        return Mage::helper('sheep_subscription')->getSaveShippingMethodUrl($this->getSubscription()->getId());
    }


    /**
     * Instructs what quote is used
     *
     * @return Mage_Sales_Model_Quote|null
     */
    public function getQuote()
    {
        return $this->getSubscription()->getQuote();
    }


    /**
     * Prevents any calls that might affect customer's checkout session
     *
     * @return null
     */
    public function getCheckout()
    {
        return null;
    }

}
