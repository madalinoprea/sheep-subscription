<?php

/**
 * Class Sheep_Subscription_Block_Subscription_Totals
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Block_Subscription_Totals extends Mage_Checkout_Block_Cart_Totals
{
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

            if ($this->subscription===null) {
                $this->subscription = $this->getData('subscription');
            }
        }

        return $this->subscription;
    }

    public function getQuote()
    {
        return $this->getSubscription()->getQuote();
    }

    /**
     * Format total value based on order currency
     *
     * @param   Varien_Object $total
     * @return  string
     */
    public function formatValue($total)
    {
        return $total->getIsFormated() ? $total->getValue() :
            Mage::app()->getStore($this->getQuote()->getStore())->formatPrice($total->getValue());
    }
}
