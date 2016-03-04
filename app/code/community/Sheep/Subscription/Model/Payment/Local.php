<?php
/**
 * Class Sheep_Subscription_Model_Payment_Local
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */

class Sheep_Subscription_Model_Payment_Local extends Sheep_Subscription_Model_Payment_Abstract
{
    /**
     * Local managed payments are not managed by the gateway.
     *
     * @return boolean
     */
    public function isGatewayManaged()
    {
       return false;
    }


    /**
     * Called after subscription is created. Creates a renewal considering that subscription was last time paid when was
     * started.
     *
     * @param Sheep_Subscription_Model_Subscription $subscription
     * @param Mage_Sales_Model_Order $order
     * @throws Exception
     */
    public function onCreateSubscription(Sheep_Subscription_Model_Subscription $subscription, Mage_Sales_Model_Order $order)
    {
        // Creates renewal
        $renewal = Mage::helper('sheep_subscription/renewal')->getRenewal($subscription, $subscription->getStartDate());
        $renewal->save();
    }
}
