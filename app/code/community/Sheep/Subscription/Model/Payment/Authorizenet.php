<?php

/**
 * Class Sheep_Subscription_Model_Payment_Authorizenet is subscription wrapper for Mage_Paygate_Model_Authorizenet
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Model_Payment_Authorizenet extends Sheep_Subscription_Model_Payment_Local
{

    /**
     * Returns configured Authorize.net API.
     *
     * @param Mage_Sales_Model_Order $order
     * @return Sheep_Subscription_Model_Payment_Authorizenet_Api
     */
    public function getApi(Mage_Sales_Model_Order $order)
    {
        return $this->payment->getExtendedApi($order->getStoreId());
    }


    /**
     * Returns card used by specified order
     *
     * @param Mage_Sales_Model_Order $order
     * @return Varien_Object
     * @throws Exception
     */
    public function getCard(Mage_Sales_Model_Order $order)
    {
        /** @var Mage_Paygate_Model_Authorizenet $paymentInstance */
        $paymentInstance = $order->getPayment()->getMethodInstance();

        $cards = $paymentInstance->getCardsStorage()->getCards();
        if (count($cards) < 1) {
            throw new Exception('Unable to find last trans id');
        }
        $card = array_shift($cards);

        return $card;
    }


    /**
     * Returns Authorize.Net transaction id referenced in specified order
     *
     * @param Mage_Sales_Model_Order $order
     * @return string
     * @throws Exception
     */
    public function getTransactionId(Mage_Sales_Model_Order $order)
    {
        $card = $this->getCard($order);

        return $card['last_trans_id'];
    }


    /**
     * After a subscription is created, we try to create an Authorize.Net customer profile and save it.
     * Saved customer profile id is going to be used during renewal order.
     *
     * @param Sheep_Subscription_Model_Subscription $subscription
     * @param Mage_Sales_Model_Order $order
     * @throws Exception
     */
    public function onCreateSubscription(Sheep_Subscription_Model_Subscription $subscription, Mage_Sales_Model_Order $order)
    {

        try {
            $transId = $this->getTransactionId($order);
            $api = $this->getApi($order);
            $customerProfileId = $api->createCustomerProfileFromTransaction($transId);

            $card = $this->getCard($order);
            $expYearMonth = sprintf('%04d-%02d-01', $card->getCcExpYear(), $card->getCcExpMonth());
            $expirationDate = date('Y-m-t', strtotime($expYearMonth));

            $paymentData = array(
                'customer_profile_id' => $customerProfileId,
            );

            /** @var Sheep_Subscription_Model_Payment $payment */
            $payment = Mage::getModel('sheep_subscription/payment');
            $payment->setInfo($paymentData);
            $payment->setSubscriptionId($subscription->getId());
            $payment->setExpirationDate($expirationDate);
            $payment->save();
        } catch (Exception $e) {
            Mage::logException($e);
        }

        // Allow to create renewal
        parent::onCreateSubscription($subscription, $order);
    }

}
