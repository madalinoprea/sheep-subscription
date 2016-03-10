<?php

/**
 * Class Sheep_Subscription_Model_Payment_Paypal_Express_Method overwrites Mage_Paypal_Model_Express to
 * handle renewals using Reference Transaction
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Model_Payment_Paypal_Express_Method extends Mage_Paypal_Model_Express
{

    /**
     * Forces customer for sign for a billing agreement if he purchases items as subscription
     *
     * @throws Mage_Core_Exception
     */
    public function validate()
    {
        parent::validate();

        $infoInstance = $this->getInfoInstance();
        /** @var Mage_Sales_Model_Quote $quote */
        $quote = $infoInstance->getQuote();

        // Verify if customer signed in for billing agreement when he purchases subscription items
        if ($quote && $quote->getPssHasSubscriptions() == Sheep_Subscription_Model_Subscription::QUOTE_HAS_SUBSCRIPTIONS_YES && !$infoInstance->getAdditionalInformation(Mage_Paypal_Model_Express_Checkout::PAYMENT_INFO_TRANSPORT_BILLING_AGREEMENT)) {
            // Validate if customer already has billing agreement
            try {
                $this->getBillingAgreement($quote->getCustomerId());
            } catch (Exception $e) {
                Mage::throwException(Mage::helper('payment')->__('You need to sign a billing agreement to purchase items as subscription'));
            }
        }
    }


    /**
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param                                $amount
     * @return Mage_Paypal_Model_Express
     */
    protected function _parentPlaceOrder(Mage_Sales_Model_Order_Payment $payment, $amount)
    {
        return parent::_placeOrder($payment, $amount);
    }


    /**
     * Overwrites parent method to allow to place order for subscription renewals using customer's
     * billing agreements
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param float $amount
     * @return Mage_Paypal_Model_Express
     */
    protected function _placeOrder(Mage_Sales_Model_Order_Payment $payment, $amount)
    {
        $isRenewal = $payment->getOrder()->getQuote()->getPssIsSubscription() == Sheep_Subscription_Model_Subscription::QUOTE_IS_SUBSCRIPTION_YES;

        return $isRenewal ? $this->_placeRenewalOrder($payment, $amount) : $this->_parentPlaceOrder($payment, $amount);
    }


    /**
     * Returns PayPal NVP api that allows to specify extra parameters
     *
     * @return Sheep_Subscription_Model_Payment_Paypal_Api_Nvp
     */
    public function getExtendedApi()
    {
        $api = Mage::getModel('sheep_subscription/payment_paypal_api_nvp');
        $api->setConfigObject($this->_pro->getConfig());

        return $api;
    }


    /**
     * Returns active PayPal billing agreement for specified customer
     *
     * @param $customerId
     * @return Mage_Sales_Model_Billing_Agreement
     * @throws
     */
    public function getBillingAgreement($customerId)
    {
        $billingAgreement = Mage::getModel('sales/billing_agreement')
            ->getAvailableCustomerBillingAgreements($customerId)
            ->addFieldToFilter('method_code', Mage_Paypal_Model_Config::METHOD_BILLING_AGREEMENT)
            ->getFirstItem();

        if (!$billingAgreement->getId()) {
            throw new Exception("customerId={$customerId} doesn't have an active billing agreement.");
        }

        return $billingAgreement;
    }


    /**
     * Used to bill a renewal with payment information stored during original order purchase.
     *
     * @see Sheep_Subscription_Model_Payment_Paypal_Express::onCreateSubscription
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param $amount
     * @return $this
     * @throws Exception
     */
    public function _placeRenewalOrder(Mage_Sales_Model_Order_Payment $payment, $amount)
    {
        $api = $this->getExtendedApi();

        /** @var Mage_Sales_Model_Order $order */
        $order = $payment->getOrder();

        /** @var Mage_Sales_Model_Quote $quote */
        $quote = $order->getQuote();

        /** @var Sheep_Subscription_Model_Subscription $subscription */
        $subscription = $quote->getPssSubscription();
        $info = $subscription->getSubscriptionPaymentInfo();

        $billingAgreement = $this->getBillingAgreement($subscription->getCustomerId());

        /** @var Sheep_Subscription_Model_Renewal $renewal */
        $renewal = $quote->getPssRenewal();


        $paypalCart = Mage::getModel('paypal/cart', array($order));
        $api->setPaypalCart($paypalCart);
        $api->setIsLineItemsEnabled(true);

        $api->setInvNum($order->getIncrementId());
        $api->setAmount($amount);
        $api->setCurrencyCode($order->getBaseCurrencyCode());
        $api->setReferenceId($billingAgreement->getReferenceId());

        $defaultRequest = array(
            'EMAIL'                    => $info['email'],
            'PAYMENTTYPE'              => 'InstantOnly',
            'REQCONFIRMSHIPPING'       => '0',
            'RISKSESSIONCORRELATIONID' => $info['correlation_id'],
            'SOFTDESCRIPTORCITY'       => Mage::getBaseUrl(),
            'DESC'                     => "Subscription #{$subscription->getId()}, Renewal {$renewal->getId()}",
            'RECURRING'                => 'N',
        );
        $api->callDoReferenceTransaction($defaultRequest);

        // Add additional info on payment
        $this->_pro->importPaymentInfo($api, $payment);
        $api->callGetTransactionDetails();
        $this->_pro->importPaymentInfo($api, $payment);

        $payment->setTransactionId($api->getTransactionId())->setIsTransactionClosed(0);
        if ($api->getBillingAgreementId()) {
            $order->addRelatedObject($billingAgreement);
            $billingAgreement->setIsObjectChanged(true);
            $billingAgreement->addOrderRelation($order);
        }

        return $this;
    }

}
