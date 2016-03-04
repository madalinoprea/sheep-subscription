<?php
/**
 * Class Sheep_Subscription_Model_Payment_Paypal_Express
 *
 * Responsible to handle
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */

class Sheep_Subscription_Model_Payment_Paypal_Express extends Sheep_Subscription_Model_Payment_Local
{

    /**
     * When subscription is created we store payment info that is required during renewal process (transaction id,
     * payer id, payer status, email and correlation_id)
     *
     * @param Sheep_Subscription_Model_Subscription $subscription
     * @param Mage_Sales_Model_Order $order
     * @throws Exception
     */
    public function onCreateSubscription(Sheep_Subscription_Model_Subscription $subscription, Mage_Sales_Model_Order $order)
    {
        parent::onCreateSubscription($subscription, $order);

        /** @var Mage_Sales_Model_Order_Payment $orderPayment */
        $orderPayment = $order->getPayment();

        // store payment info
        $info = array(
            'referenceid' => $orderPayment->getLastTransId(),
            'payer_id' => $orderPayment->getAdditionalInformation('paypal_payer_id'),
            'payer_status' => $orderPayment->getAdditionalInformation('paypal_payer_status'),
            'email' => $orderPayment->getAdditionalInformation('paypal_payer_email'),
            'correlation_id' => $orderPayment->getAdditionalInformation('paypal_correlation_id'),
        );

        /** @var  $payment */
        $payment = Mage::getModel('sheep_subscription/payment');
        $payment->setInfo($info);
        $payment->setSubscriptionId($subscription->getId());
        $payment->save();
    }
}
