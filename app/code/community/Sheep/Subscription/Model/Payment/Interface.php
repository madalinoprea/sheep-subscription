<?php
/**
 * Class Sheep_Subscription_Model_Payment_Interface
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */

interface Sheep_Subscription_Model_Payment_Interface
{

    /**
     * Sets payment method
     * @param Mage_Payment_Model_Method_Abstract $payment
     * @return mixed
     */
    public function setPayment(Mage_Payment_Model_Method_Abstract $payment);

    /**
     * Returns payment method title
     * @return string
     */
    public function getTitle();

    /**
     * Returns payment method code
     * @return string
     */
    public function getCode();

    /**
     * TODO: do we need to know if this is gateway managed or not?
     * @return boolean
     */
    public function isGatewayManaged();

    /**
     * Called after subscription is created to allow payment to create a renewal and save additional payment info
     *
     * @param Sheep_Subscription_Model_Subscription $subscription
     * @param Mage_Sales_Model_Order $order
     */
    public function onCreateSubscription(Sheep_Subscription_Model_Subscription $subscription, Mage_Sales_Model_Order $order);
}
