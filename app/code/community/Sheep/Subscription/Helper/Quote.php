<?php

/**
 * Class Sheep_Subscription_Helper_Quote
 *
 * @category Sheep
 * @package Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Helper_Quote extends Mage_Core_Helper_Abstract
{

    /**
     * Checks if specified cart item was added as subscription
     *
     * @param Mage_Sales_Model_Quote_Item $quoteItem
     * @return bool
     */
    public function isSubscriptionQuoteItem(Mage_Sales_Model_Quote_Item $quoteItem)
    {
        $options = $quoteItem->getBuyRequest()->getData('options');
        return $options && array_key_exists(Sheep_Subscription_Model_Service::SUBSCRIPTION_PRODUCT_OPTION_ID, $options);
    }


    /**
     * Returns subscription type id from quote item
     *
     * @param Mage_Sales_Model_Quote_Item $quoteItem
     * @return int
     */
    public function getSubscriptionTypeId(Mage_Sales_Model_Quote_Item $quoteItem)
    {
        $typeId = null;
        $options = $quoteItem->getBuyRequest()->getData('options');
        if ($options && array_key_exists(Sheep_Subscription_Model_Service::SUBSCRIPTION_PRODUCT_OPTION_ID, $options)) {
            $typeId = (int)str_replace(Sheep_Subscription_Model_Service::SUBSCRIPTION_PRODUCT_OPTION_TYPE_VALUE_ID_PREFIX, '', $options[Sheep_Subscription_Model_Service::SUBSCRIPTION_PRODUCT_OPTION_ID]);
        }

        return $typeId;
    }


    /**
     * Returns subscription type associated to quote item
     *
     * @param Mage_Sales_Model_Quote_Item $quoteItem
     * @return Sheep_Subscription_Model_Type|null
     */
    public function getSubscriptionType(Mage_Sales_Model_Quote_Item $quoteItem)
    {
        $type = null;
        $typeId = $this->getSubscriptionTypeId($quoteItem);
        if ($typeId) {
            $type = Mage::getModel('sheep_subscription/type')->load($typeId);
            if (!$type->getId()) {
                $type = null;
            }
        }

        return $type;
    }

}
