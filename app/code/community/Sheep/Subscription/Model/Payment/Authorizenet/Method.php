<?php

/**
 * Class Sheep_Subscription_Model_Payment_Authorizenet_Method
 *
 * @category Sheep
 * @package Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Model_Payment_Authorizenet_Method extends Mage_Paygate_Model_Authorizenet
{

    /**
     * We bypass validations if this method is attached to a subscription quote.
     * We assume we have a customer profile that is going to be used instead of usual credit card info.
     *
     * @return Mage_Payment_Model_Abstract
     */
    public function validate()
    {
        return $this->isPartOfRenewalOrder() ? $this : parent::validate();
    }


    /**
     * Delegates auth and authCapture api calls for renewal orders to our extended API that knows to charge a customer profile id
     *
     * @param Varien_Object $request
     * @return Mage_Paygate_Model_Authorizenet_Result
     * @throws Exception
     */
    protected function _postRequest(Varien_Object $request)
    {
        // Identify if this a request we need to handle (auth or authCapture)
        if ($this->isPartOfRenewalOrder() &&
            ($request->getXType() == Mage_Paygate_Model_Authorizenet::REQUEST_TYPE_AUTH_CAPTURE || $request->getXType() == Mage_Paygate_Model_Authorizenet::REQUEST_TYPE_AUTH_ONLY)
        ) {

            $response = $this->_postRenewalRequest($request);
            return $this->_getResult($response);
        }

        return parent::_postRequest($request);
    }


    /**
     * Returns a configured instanced of our Authorize.Net Transaction Details API
     *
     * @param null $storeId
     * @return Sheep_Subscription_Model_Payment_Authorizenet_Api
     */
    public function getExtendedApi($storeId = null)
    {
        $uri = $this->getConfigData('cgi_url_td', $storeId);

        /** @var Sheep_Subscription_Model_Payment_Authorizenet_Api $api */
        $api = Mage::getModel('sheep_subscription/payment_authorizenet_api');
        $api->setApiEndPoint($uri ?: Mage_Paygate_Model_Authorizenet::CGI_URL_TD);
        $api->setLogin($this->getConfigData('login', $storeId));
        $api->setTransKey($this->getConfigData('trans_key', $storeId));

        return $api;
    }


    /**
     * Checks if current quote is a subscription quote
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return bool
     */
    public function isRenewalQuote(Mage_Sales_Model_Quote $quote)
    {
        return $quote && $quote->getPssIsSubscription() == Sheep_Subscription_Model_Subscription::QUOTE_IS_SUBSCRIPTION_YES;
    }


    /**
     * Checks if current instance is using an order that is created from a subscription quote (renewal order)
     *
     * @return bool
     */
    public function isPartOfRenewalOrder()
    {
        $order = $this->getInfoInstance()->getOrder();

        return $order && $this->isRenewalQuote($order->getQuote());
    }


    /**
     * Use payment information stored from @see Sheep_Subscription_Model_Payment_Authorizenet::onCreateSubscription to auth/capture
     * a renewal order.
     *
     * @param Mage_Paygate_Model_Authorizenet_Request $request
     * @return SimpleXMLElement
     * @throws Exception
     */
    protected function _postRenewalRequest(Mage_Paygate_Model_Authorizenet_Request $request)
    {
        // Fetch customer profile id
        $payment = $this->getInfoInstance();
        /** @var Mage_Sales_Model_Order $order */
        $order = $payment->getOrder();
        /** @var Mage_Sales_Model_Quote $quote */
        $quote = $order->getQuote();

        /** @var Sheep_Subscription_Model_Subscription $subscription */
        $subscription = $quote->getPssSubscription();
        $info = $subscription->getSubscriptionPaymentInfo();

        if (!$info) {
            throw new Exception('Payment info missing on subscription');
        }

        $api = $this->getExtendedApi($order->getStoreId());
        return $api->chargeCustomerProfile($info['customer_profile_id'], $request);
    }


    /**
     * Transforms Authorize.Net createTransactionResponse into AIM Transaction Response
     *
     * @param SimpleXMLElement $transactionResponse
     * @return Mage_Paygate_Model_Authorizenet_Result
     */
    protected function _getResult(SimpleXMLElement $transactionResponse)
    {
        $result = Mage::getModel('paygate/authorizenet_result');

        $result->setResponseCode((int)$transactionResponse->responseCode)
            ->setResponseReasonCode((string)$transactionResponse->messages->message->code)
            ->setResponseReasonText((string)$transactionResponse->messages->message->description)
            ->setApprovalCode((string)$transactionResponse->authCode)
            ->setAvsResultCode((string)$transactionResponse->avsResultCode)
            ->setTransactionId((string)$transactionResponse->transId)
            ->setMd5Hash((string)$transactionResponse->transHash)
            ->setCardCodeResponseCode((string)$transactionResponse->cvvResultCode)
            ->setCAVVResponseCode((string)$transactionResponse->cavvResultCode)
            ->setAccNumber((string)$transactionResponse->accountNumber)
            ->setCardType((string)$transactionResponse->accountType);

        return $result;
    }

}
