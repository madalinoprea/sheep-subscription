<?php

/**
 * Class Sheep_Subscription_Model_Quote_Address_Total_Subscription
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Model_Quote_Address_Total_Subscription extends Mage_Sales_Model_Quote_Address_Total_Abstract
{

    /**
     * Updates quote pss_has_subscriptions (quote attribute that keeps track if there are subscription items in this cart)
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @return $this|Mage_Sales_Model_Quote_Address_Total_Abstract
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);
        $hasSubscriptionProducts = Sheep_Subscription_Model_Subscription::QUOTE_HAS_SUBSCRIPTIONS_NO;
        /** @var Sheep_Subscription_Helper_Quote $productHelper */
        $productHelper = Mage::helper('sheep_subscription/quote');

        $items = $this->_getAddressItems($address);
        foreach ($items as $quoteItem) {
            if ($productHelper->isSubscriptionQuoteItem($quoteItem)) {
                $hasSubscriptionProducts = Sheep_Subscription_Model_Subscription::QUOTE_HAS_SUBSCRIPTIONS_YES;
                break;
            }
        }

        $address->getQuote()->setPssHasSubscriptions($hasSubscriptionProducts);

        return $this;
    }

}
