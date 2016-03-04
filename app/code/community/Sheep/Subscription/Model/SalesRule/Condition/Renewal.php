<?php

/**
 * Class Sheep_Subscription_Model_SalesRule_Condition_Renewal
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2016, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Model_SalesRule_Condition_Renewal extends Sheep_Subscription_Model_SalesRule_Condition_Base
{
    const CONDITION_ATTRIBUTE_SUCCESSFUL_RENEWALS = 'pss_renewals';


    /**
     * Attributes related to renewals
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $attributes = array(self::CONDITION_ATTRIBUTE_SUCCESSFUL_RENEWALS => 'Successful Renewals',);
        $this->setAttributeOption($attributes);

        return $this;
    }


    /**
     * Treat provided value as number
     *
     * @return string
     */
    public function getInputType()
    {
        return 'numeric';
    }


    /**
     * Offer a text box to enter number of successful renewals
     *
     * @return string
     */
    public function getValueElementType()
    {
        return 'text';
    }


    /**
     * Validates against number of payed renewals
     *
     * $object can be Mage_Sales_Model_Quote_Address
     *
     * @param Varien_Object $object
     * @return bool
     */
    public function validate(Varien_Object $object)
    {
        /** @var Sheep_Subscription_Helper_Renewal $helper */
        $helper = Mage::helper('sheep_subscription/renewal');
        $customerId = $this->getCustomerId($object);

        if (!$customerId) {
            return false;
        }

        $renewals = $helper->getCustomerRenewals($customerId);
        $renewals->addStatusFilter(Sheep_Subscription_Model_Renewal::STATUS_PAYED);
        $count = $renewals->getSize();

        return $this->validateAttribute($count);
    }

}
