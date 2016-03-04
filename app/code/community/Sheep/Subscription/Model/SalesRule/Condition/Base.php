<?php

/**
 * Class Sheep_Subscription_Model_SalesRule_Condition_Base
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2016, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Model_SalesRule_Condition_Base extends Mage_Rule_Model_Condition_Abstract
{

    /**
     * Returns customer id based on validated object
     *
     * @param Varien_Object $object
     * @return int|null
     */
    public function getCustomerId(Varien_Object $object)
    {
        $customerId = null;

        if ($object instanceof Mage_Sales_Model_Quote_Address) {
            $customerId = $object->getQuote()->getCustomerId();
        }

        return $customerId;
    }

}
