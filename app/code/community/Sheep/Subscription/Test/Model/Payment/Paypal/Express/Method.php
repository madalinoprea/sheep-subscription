<?php

/**
 * Class Sheep_Subscription_Test_Model_Payment_Paypal_Express_Method
 *
 * @category Sheep
 * @package  Sheep_$
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers   Sheep_Subscription_Model_Payment_Paypal_Express_Method
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Model_Payment_Paypal_Express_Method extends EcomDev_PHPUnit_Test_Case
{


    /**
     * Occurring during place order flow when payment gets a sales payment
     */
    public function testValidateWithoutQuote()
    {
        $infoInstance = $this->getModelMock('sales/order_payment', array('getOrder'));
        $infoInstance->expects($this->once())->method('getOrder')->willReturn(
            new Varien_Object(
                array('billing_address' => new Varien_Object())
            ));

        $model = $this->getModelMock('sheep_subscription/payment_paypal_express_method', array('canUseForCountry', 'getInfoInstance'));
        $model->expects($this->any())->method('canUseForCountry')->willReturn(true);
        $model->expects($this->any())->method('getInfoInstance')->willReturn($infoInstance);

        $model->validate();
    }

    public function testValidateWithoutSubscriptionItems()
    {
        $quote = $this->getModelMock('sales/quote', array('getPssHasSubscriptions', 'getBillingAddress'));
        $quote->expects($this->once())->method('getPssHasSubscriptions')->willReturn(Sheep_Subscription_Model_Subscription::QUOTE_HAS_SUBSCRIPTIONS_NO);
        $quote->expects($this->once())->method('getBillingAddress')->willReturn(new Varien_Object());

        $infoInstance = $this->getModelMock('sales/quote_payment', array('getQuote'));
        $infoInstance->expects($this->any())->method('getQuote')->willReturn($quote);

        $model = $this->getModelMock('sheep_subscription/payment_paypal_express_method', array('canUseForCountry', 'getInfoInstance'));
        $model->expects($this->any())->method('canUseForCountry')->willReturn(true);
        $model->expects($this->any())->method('getInfoInstance')->willReturn($infoInstance);

        $model->validate();
    }

    public function testValidateWithSubscriptionItemsAndNewBillingAgreement()
    {
        $quote = $this->getModelMock('sales/quote', array('getPssHasSubscriptions', 'getBillingAddress'));
        $quote->expects($this->once())->method('getPssHasSubscriptions')->willReturn(Sheep_Subscription_Model_Subscription::QUOTE_HAS_SUBSCRIPTIONS_YES);
        $quote->expects($this->once())->method('getBillingAddress')->willReturn(new Varien_Object());

        $infoInstance = $this->getModelMock('sales/quote_payment', array('getQuote', 'getAdditionalInformation'));
        $infoInstance->expects($this->any())->method('getQuote')->willReturn($quote);
        $infoInstance->expects($this->once())->method('getAdditionalInformation')->with('paypal_ec_create_ba')->willReturn(1);

        $model = $this->getModelMock('sheep_subscription/payment_paypal_express_method', array('canUseForCountry', 'getInfoInstance'));
        $model->expects($this->any())->method('canUseForCountry')->willReturn(true);
        $model->expects($this->any())->method('getInfoInstance')->willReturn($infoInstance);

        $model->validate();
    }

    public function testValidateWithSubscriptionItemsAndExistingBillingAgreement()
    {
        $quote = $this->getModelMock('sales/quote', array('getPssHasSubscriptions', 'getBillingAddress', 'getCustomerId'));
        $quote->expects($this->once())->method('getPssHasSubscriptions')->willReturn(Sheep_Subscription_Model_Subscription::QUOTE_HAS_SUBSCRIPTIONS_YES);
        $quote->expects($this->once())->method('getBillingAddress')->willReturn(new Varien_Object());
        $quote->expects($this->once())->method('getCustomerId')->willReturn(1000);

        $infoInstance = $this->getModelMock('sales/quote_payment', array('getQuote', 'getAdditionalInformation'));
        $infoInstance->expects($this->any())->method('getQuote')->willReturn($quote);
        $infoInstance->expects($this->once())->method('getAdditionalInformation')->with('paypal_ec_create_ba')->willReturn(0);

        $model = $this->getModelMock('sheep_subscription/payment_paypal_express_method', array('canUseForCountry', 'getInfoInstance', 'getBillingAgreement'));
        $model->expects($this->any())->method('canUseForCountry')->willReturn(true);
        $model->expects($this->any())->method('getInfoInstance')->willReturn($infoInstance);
        $model->expects($this->once())->method('getBillingAgreement')->with(1000)->willReturn(new Varien_Object());

        $model->validate();
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage You need to sign a billing agreement
     */
    public function testValidateWithSubscriptionItemsAndWithoutBillingAgreement()
    {
        $quote = $this->getModelMock('sales/quote', array('getPssHasSubscriptions', 'getBillingAddress', 'getCustomerId'));
        $quote->expects($this->once())->method('getPssHasSubscriptions')->willReturn(Sheep_Subscription_Model_Subscription::QUOTE_HAS_SUBSCRIPTIONS_YES);
        $quote->expects($this->once())->method('getBillingAddress')->willReturn(new Varien_Object());
        $quote->expects($this->once())->method('getCustomerId')->willReturn(1000);

        $infoInstance = $this->getModelMock('sales/quote_payment', array('getQuote', 'getAdditionalInformation'));
        $infoInstance->expects($this->any())->method('getQuote')->willReturn($quote);
        $infoInstance->expects($this->once())->method('getAdditionalInformation')->with('paypal_ec_create_ba')->willReturn(0);

        $model = $this->getModelMock('sheep_subscription/payment_paypal_express_method', array('canUseForCountry', 'getInfoInstance', 'getBillingAgreement'));
        $model->expects($this->any())->method('canUseForCountry')->willReturn(true);
        $model->expects($this->any())->method('getInfoInstance')->willReturn($infoInstance);
        $model->expects($this->once())->method('getBillingAgreement')->with(1000)->willThrowException(new Exception('Agreement not found'));

        // We expect an exception is not throwned
        $model->validate();
    }

    public function testPlaceOrder()
    {
        $quote = $this->getModelMock('sales/quote', array('getPssIsSubscription'));
        $quote->expects($this->any())->method('getPssHasSubscriptions')->willReturn(Sheep_Subscription_Model_Subscription::QUOTE_IS_SUBSCRIPTION_NO);

        $order = $this->getModelMock('sales/order', array('getQuote'));
        $order->expects($this->any())->method('getQuote')->willReturn($quote);

        $payment = $this->getModelMock('sales/order_payment', array('getOrder'));
        $payment->expects($this->any())->method('getOrder')->willReturn($order);

        $model = $this->getModelMock('sheep_subscription/payment_paypal_express_method', array('_placeRenewalOrder', '_parentPlaceOrder'));
        $model->expects($this->never())->method('_placeRenewalOrder');
        $model->expects($this->once())->method('_parentPlaceOrder')->with($payment, 256);

        EcomDev_Utils_Reflection::invokeRestrictedMethod($model, '_placeOrder', array($payment, 256));
    }

    public function testPlaceOrderForRenewal()
    {
        $nvpApiMock = $this->getModelMock('paypal/api_nvp', array('callDoExpressCheckoutPayment'));
        $nvpApiMock->expects($this->never())->method('callDoExpressCheckoutPayment');
        $this->replaceByMock('model', 'paypal/api_nvp', $nvpApiMock);

        $quote = $this->getModelMock('sales/quote', array('getPssIsSubscription'));
        $quote->expects($this->any())->method('getPssIsSubscription')->willReturn(Sheep_Subscription_Model_Subscription::QUOTE_IS_SUBSCRIPTION_YES);

        $order = $this->getModelMock('sales/order', array('getQuote'));
        $order->expects($this->any())->method('getQuote')->willReturn($quote);

        $payment = $this->getModelMock('sales/order_payment', array('getOrder'));
        $payment->expects($this->any())->method('getOrder')->willReturn($order);

        $model = $this->getModelMock('sheep_subscription/payment_paypal_express_method', array('_placeRenewalOrder', '_importToPayment'));
        $model->expects($this->once())->method('_placeRenewalOrder')->with($payment, 256);
        $model->expects($this->never())->method('_importToPayment')->with($nvpApiMock, $payment);

        EcomDev_Utils_Reflection::invokeRestrictedMethod($model, '_placeOrder', array($payment, 256));
    }

    public function testGetExtendedApi()
    {
        $model = $this->getModelMock('sheep_subscription/payment_paypal_express_method', array('_placeOrder'));
        $api = $model->getExtendedApi();
        $this->assertNotNull($api);
        $this->assertInstanceOf('Sheep_Subscription_Model_Payment_Paypal_Api_Nvp', $api);
    }

    public function testGetBillingAgreement()
    {
        $agreements = $this->getResourceModelMock('sales/billing_agreement_collection', array('addFieldToFilter', 'setOrder', 'load', 'getFirstItem'));
        $agreements->expects($this->at(0))->method('addFieldToFilter')->with('customer_id', 1001)->willReturnSelf();
        $agreements->expects($this->at(1))->method('addFieldToFilter')->with('status', 'active')->willReturnSelf();
        $agreements->expects($this->any())->method('addFieldToFilter')->willReturnSelf();
        $agreements->expects($this->once())->method('getFirstItem')->willReturn(new Varien_Object(array('id' => 200)));
        $this->replaceByMock('resource_model', 'sales/billing_agreement_collection', $agreements);

        $model = $this->getModelMock('sheep_subscription/payment_paypal_express_method', array('_placeOrder', 'getApi'));
        $actual = $model->getBillingAgreement(1001);
        $this->assertNotNull($actual);
        $this->assertEquals(200, $actual->getId());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage customerId=1001 doesn't have an active billing agreement
     */
    public function testGetBillingAgreementWithError()
    {
        $agreements = $this->getResourceModelMock('sales/billing_agreement_collection', array('addFieldToFilter', 'setOrder', 'load', 'getFirstItem'));
        $agreements->expects($this->at(0))->method('addFieldToFilter')->with('customer_id', 1001)->willReturnSelf();
        $agreements->expects($this->at(1))->method('addFieldToFilter')->with('status', 'active')->willReturnSelf();
        $agreements->expects($this->any())->method('addFieldToFilter')->willReturnSelf();
        $agreements->expects($this->once())->method('getFirstItem')->willReturn(new Varien_Object(array('id' => '')));
        $this->replaceByMock('resource_model', 'sales/billing_agreement_collection', $agreements);

        $model = $this->getModelMock('sheep_subscription/payment_paypal_express_method', array('_placeOrder', 'getApi'));
        $model->getBillingAgreement(1001);
    }

    /**
     * Renewal order #10000001 (grand total 256) is created for renewal #501 associated to subscription #203 that belongs to customer #1001.
     * Customer #1001.
     *
     * Transaction T-100100 will be created on PayPal
     */
    public function testPlaceRenewalOrder()
    {
        $api = $this->getModelMock('sheep_subscription/payment_paypal_api_nvp', array(
            'setPaypalCart', 'setIsLineItemsEnabled', 'setInvNum', 'setAmount', 'setCurrencyCode', 'setReferenceId',
            'getBillingAgreementId', 'getTransactionId', 'callDoReferenceTransaction', 'callGetTransactionDetails'
        ));

        $api->expects($this->once())->method('setPaypalCart');
        $api->expects($this->once())->method('setIsLineItemsEnabled')->with(true);
        $api->expects($this->once())->method('setInvNum')->with(10000001);
        $api->expects($this->once())->method('setAmount')->with(256);
        $api->expects($this->once())->method('setCurrencyCode')->with('EUR');
        $api->expects($this->once())->method('setReferenceId')->with('B-100100');
        $api->expects($this->once())->method('callDoReferenceTransaction');
        $api->expects($this->once())->method('callGetTransactionDetails');
        $api->expects($this->any())->method('getBillingAgreementId')->willReturn('B-100100');
        $api->expects($this->any())->method('getTransactionId')->willReturn('T-100100');

        $renewal = $this->getModelMock('sheep_subscription/renewal', array('getId'));
        $renewal->expects($this->any())->method('getId')->willReturn(501);

        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getId', 'getCustomerId', 'getSubscriptionPaymentInfo'));
        $subscription->expects($this->any())->method('getId')->willReturn(203);
        $subscription->expects($this->any())->method('getCustomerId')->willReturn(1001);
        $subscription->expects($this->any())->method('getSubscriptionPaymentInfo')->willReturn(array(
            'email'          => 'mario@moprea.ro',
            'correlation_id' => 'C-100100',
        ));

        $quote = $this->getModelMock('sales/quote', array('getPssSubscription', 'getPssRenewal'));
        $quote->expects($this->any())->method('getPssSubscription')->willReturn($subscription);
        $quote->expects($this->any())->method('getPssRenewal')->willReturn($renewal);


        $order = $this->getModelMock('sales/order', array('getQuote', 'getIncrementId', 'getBaseCurrencyCode', 'addRelatedObject'));
        $order->expects($this->any())->method('getQuote')->willReturn($quote);
        $order->expects($this->any())->method('getIncrementId')->willReturn(10000001);
        $order->expects($this->any())->method('getBaseCurrencyCode')->willReturn('EUR');
        $order->expects($this->once())->method('addRelatedObject');

        $payment = $this->getModelMock('sales/order_payment', array('getOrder', 'setTransactionId', 'setIsTransactionClosed'));
        $payment->expects($this->any())->method('getOrder')->willReturn($order);
        $payment->expects($this->once())->method('setTransactionId')->with()->willReturnSelf('T-100100');
        $payment->expects($this->once())->method('setIsTransactionClosed')->with(0);

        $proMock = $this->getModelMock('paypal/pro', array('importPaymentInfo'));
        $proMock->expects($this->at(0))->method('importPaymentInfo')->with($api, $payment);
        $proMock->expects($this->at(1))->method('importPaymentInfo')->with($api, $payment);
        $this->replaceByMock('model', 'paypal/pro', $proMock);


        $billingAgreement = $this->getModelMock('sales/billing_agreement', array('getReferenceId', 'setIsObjectChanged', 'addOrderRelation'));
        $billingAgreement->expects($this->any())->method('getReferenceId')->willReturn('B-100100');
        $billingAgreement->expects($this->once())->method('setIsObjectChanged')->with(true);
        $billingAgreement->expects($this->once())->method('addOrderRelation')->with($order);

        $model = $this->getModelMock('sheep_subscription/payment_paypal_express_method', array('getExtendedApi', 'getBillingAgreement'));
        $model->expects($this->once())->method('getExtendedApi')->willReturn($api);
        $model->expects($this->once())->method('getBillingAgreement')->with()->willReturn($billingAgreement);

        EcomDev_Utils_Reflection::invokeRestrictedMethod($model, '_placeRenewalOrder', array($payment, 256));
    }

}
