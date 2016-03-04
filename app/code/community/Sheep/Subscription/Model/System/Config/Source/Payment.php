<?php
/**
 * Class Sheep_Subscription_Model_System_Config_Source_Payment
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */

class Sheep_Subscription_Model_System_Config_Source_Payment
{
    public function toOptionArray()
    {
        $options = array();
        $methods = Mage::helper('sheep_subscription/payment')->getActiveSubscriptionPaymentMethods();

        foreach ($methods as $method) {
            $options[] = array(
                'value' => $method->getCode(),
                'label' => $method->getTitle()
            );
        }

        return $options;
    }

}
