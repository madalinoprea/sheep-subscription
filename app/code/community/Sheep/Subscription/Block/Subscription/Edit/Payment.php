<?php

/**
 * Class Sheep_Subscription_Block_Subscription_Edit_Payment
 *
 * @category Sheep
 * @package Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Block_Subscription_Edit_Payment extends Sheep_Subscription_Block_Subscription_Abstract
{

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
     * Returns url that will add current products from specified subscription to cart
     *
     * @return string
     */
    public function getAddSubscriptionToCartUrl()
    {
        return Mage::helper('sheep_subscription')->getAddSubscriptionToCartUrl($this->getSubscription()->getId());
    }
}
