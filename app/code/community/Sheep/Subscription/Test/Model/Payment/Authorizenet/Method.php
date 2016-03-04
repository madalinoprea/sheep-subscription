<?php

/**
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_Model_Payment_Authorizenet_Method
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Model_Payment_Authorizenet_Method extends EcomDev_PHPUnit_Test_Case
{
    /** @var Sheep_Subscription_Model_Payment_Authorizenet_Method */
    protected $model;

    protected function setUp()
    {
        $this->model = Mage::getModel('sheep_subscription/payment_authorizenet_method');
    }


    public function testValidate()
    {
        $model = $this->getModelMock('sheep_subscription/payment_authorizenet_method', array('isPartOfRenewalOrder', 'getInfoInstance'));
        $model->expects($this->never())->method('getInfoInstance');
        $model->expects($this->once())->method('isPartOfRenewalOrder')->willReturn(true);
        $actual = $model->validate();
        $this->assertEquals($model, $actual);
    }


    /**
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Incorrect credit card expiration date.
     */
    public function testValidateWithoutRenewalOrder()
    {
        $quote = $this->getModelMock('sales/quote', array('load'));

        $infoInstance = $this->getModelMock('sales/quote_payment', array('getQuote', 'getCcNumber'));
        $infoInstance->expects($this->atLeastOnce())->method('getQuote')->willReturn($quote);
        $infoInstance->expects($this->atLeastOnce())->method('getCcNumber')->willReturn('123');

        $model = $this->getModelMock('sheep_subscription/payment_authorizenet_method', array('isPartOfRenewalOrder', 'getInfoInstance'));
        $model->expects($this->atLeastOnce())->method('getInfoInstance')->willReturn($infoInstance);
        $model->expects($this->once())->method('isPartOfRenewalOrder')->willReturn(false);
        $actual = $model->validate();
        $this->assertEquals($model, $actual);
    }

    public function requestTypeProvider()
    {
        return array(
            array(Mage_Paygate_Model_Authorizenet::REQUEST_TYPE_AUTH_CAPTURE),
            array(Mage_Paygate_Model_Authorizenet::REQUEST_TYPE_AUTH_ONLY)
        );
    }

    /**
     * @dataProvider requestTypeProvider
     * @param $requestType
     */
    public function testPostRequest($requestType)
    {
        $request = Mage::getModel('paygate/authorizenet_request');
        $request->setXType($requestType);

        $response = new SimpleXMLElement('<xml/>');

        $model = $this->getModelMock('sheep_subscription/payment_authorizenet_method', array('isPartOfRenewalOrder', '_postRenewalRequest', '_getResult'));
        $model->expects($this->once())->method('isPartOfRenewalOrder')->willReturn(true);
        $model->expects($this->once())->method('_postRenewalRequest')->with($request)->willReturn($response);
        $model->expects($this->once())->method('_getResult')->with($response);

        EcomDev_Utils_Reflection::invokeRestrictedMethod($model, '_postRequest', array($request));
    }


    public function testGetExtendedApi()
    {
        $api = $this->getModelMock('sheep_subscription/payment_authorizenet_api', array('setApiEndPoint', 'setLogin', 'setTransKey'));
        $api->expects($this->once())->method('setApiEndPoint')->with('api_url');
        $api->expects($this->once())->method('setLogin')->with('api_login');
        $api->expects($this->once())->method('setTransKey')->with('api_key');
        $this->replaceByMock('model', 'sheep_subscription/payment_authorizenet_api', $api);

        $model = $this->getModelMock('sheep_subscription/payment_authorizenet_method', array('getConfigData', '_postRenewalRequest', '_getResult'));
        $model->expects($this->at(0))->method('getConfigData')->with('cgi_url_td', 10)->willReturn('api_url');
        $model->expects($this->at(1))->method('getConfigData')->with('login', 10)->willReturn('api_login');
        $model->expects($this->at(2))->method('getConfigData')->with('trans_key', 10)->willReturn('api_key');

        $actual = $model->getExtendedApi(10);
        $this->assertEquals($api, $actual);
    }


    public function testIsRenewalQuote()
    {
        $quote = $this->getModelMock('sales/quote', array('getPssIsSubscription'));
        $quote->expects($this->any())->method('getPssIsSubscription')->willReturn(Sheep_Subscription_Model_Subscription::QUOTE_IS_SUBSCRIPTION_YES);
        $actual = $this->model->isRenewalQuote($quote);

        $this->assertTrue($actual);
    }


    public function testIsRenewalQuoteNo()
    {
        $quote = $this->getModelMock('sales/quote', array('getPssIsSubscription'));
        $quote->expects($this->any())->method('getPssIsSubscription')->willReturn(Sheep_Subscription_Model_Subscription::QUOTE_IS_SUBSCRIPTION_NO);
        $actual = $this->model->isRenewalQuote($quote);

        $this->assertFalse($actual);
    }


    public function testIsPartOfRenewalOrder()
    {
        $orderQuote = $this->getModelMock('sales/quote', array('load'));

        $order = $this->getModelMock('sales/order', array('getQuote'));
        $order->expects($this->once())->method('getQuote')->willReturn($orderQuote);

        $infoInstance = $this->getModelMock('payment/info', array('getOrder'));
        $infoInstance->expects($this->any())->method('getOrder')->willReturn($order);

        $model = $this->getModelMock('sheep_subscription/payment_authorizenet_method', array('isRenewalQuote', 'getInfoInstance'));
        $model->expects($this->any())->method('getInfoInstance')->willReturn($infoInstance);
        $model->expects($this->once())->method('isRenewalQuote')->with($orderQuote)->willReturn(true);

        $actual = $model->isPartofRenewalOrder();
        $this->assertTrue($actual);
    }


    public function testIsPartOfRenewalOrderNo()
    {
        $orderQuote = $this->getModelMock('sales/quote', array('load'));

        $order = $this->getModelMock('sales/order', array('getQuote'));
        $order->expects($this->once())->method('getQuote')->willReturn($orderQuote);

        $infoInstance = $this->getModelMock('payment/info', array('getOrder'));
        $infoInstance->expects($this->any())->method('getOrder')->willReturn($order);

        $model = $this->getModelMock('sheep_subscription/payment_authorizenet_method', array('isRenewalQuote', 'getInfoInstance'));
        $model->expects($this->any())->method('getInfoInstance')->willReturn($infoInstance);
        $model->expects($this->once())->method('isRenewalQuote')->with($orderQuote)->willReturn(false);

        $actual = $model->isPartofRenewalOrder();
        $this->assertFalse($actual);
    }


    public function testIsPartOfRenewalOrderNoOrder()
    {
        $infoInstance = $this->getModelMock('payment/info', array('getOrder'));
        $infoInstance->expects($this->any())->method('getOrder')->willReturn(null);

        $model = $this->getModelMock('sheep_subscription/payment_authorizenet_method', array('isRenewalQuote', 'getInfoInstance'));
        $model->expects($this->any())->method('getInfoInstance')->willReturn($infoInstance);
        $model->expects($this->never())->method('isRenewalQuote');

        $actual = $model->isPartofRenewalOrder();
        $this->assertFalse($actual);
    }


    public function testPostRenewalRequest()
    {
        // Api mocks
        $request = Mage::getModel('paygate/authorizenet_request');
        $response = new SimpleXMLElement('<xml/>');

        $api = $this->getModelMock('sheep_subscription/payment_authorizenet_api', array('chargeCustomerProfile'));
        $api->expects($this->once())->method('chargeCustomerProfile')->with(10001, $request)->willReturn($response);

        // order mocks
        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getSubscriptionPaymentInfo'));
        $subscription->expects($this->atLeastOnce())->method('getSubscriptionPaymentInfo')->willReturn(
            array('customer_profile_id' => 10001)
        );

        $orderQuote = $this->getModelMock('sales/quote', array('getPssSubscription'));
        $orderQuote->expects($this->any())->method('getPssSubscription')->willReturn($subscription);

        $order = $this->getModelMock('sales/order', array('getQuote', 'getStoreId'));
        $order->expects($this->once())->method('getQuote')->willReturn($orderQuote);
        $order->expects($this->any())->method('getStoreId')->willReturn(10);

        $infoInstance = $this->getModelMock('payment/info', array('getOrder'));
        $infoInstance->expects($this->any())->method('getOrder')->willReturn($order);

        // test
        $model = $this->getModelMock('sheep_subscription/payment_authorizenet_method', array('getInfoInstance', 'getExtendedApi'));
        $model->expects($this->once())->method('getInfoInstance')->willReturn($infoInstance);
        $model->expects($this->once())->method('getExtendedApi')->with(10)->willReturn($api);

        $actual = EcomDev_Utils_Reflection::invokeRestrictedMethod($model, '_postRenewalRequest', array($request));
        $this->assertEquals($response, $actual);
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Payment info missing on subscription
     */
    public function testPostRenewalRequestWithoutPaymentInfo()
    {
        // Api mocks
        $request = Mage::getModel('paygate/authorizenet_request');

        $api = $this->getModelMock('sheep_subscription/payment_authorizenet_api', array('chargeCustomerProfile'));
        $api->expects($this->never())->method('chargeCustomerProfile');

        // order mocks
        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getSubscriptionPaymentInfo'));
        $subscription->expects($this->atLeastOnce())->method('getSubscriptionPaymentInfo')->willReturn(array());

        $orderQuote = $this->getModelMock('sales/quote', array('getPssSubscription'));
        $orderQuote->expects($this->any())->method('getPssSubscription')->willReturn($subscription);

        $order = $this->getModelMock('sales/order', array('getQuote', 'getStoreId'));
        $order->expects($this->once())->method('getQuote')->willReturn($orderQuote);
        $order->expects($this->any())->method('getStoreId')->willReturn(10);

        $infoInstance = $this->getModelMock('payment/info', array('getOrder'));
        $infoInstance->expects($this->any())->method('getOrder')->willReturn($order);

        // test
        $model = $this->getModelMock('sheep_subscription/payment_authorizenet_method', array('getInfoInstance', 'getExtendedApi'));
        $model->expects($this->any())->method('getInfoInstance')->willReturn($infoInstance);
        $model->expects($this->never())->method('getExtendedApi');

        EcomDev_Utils_Reflection::invokeRestrictedMethod($model, '_postRenewalRequest', array($request));
    }


    public function testGetResult()
    {
        $response = new SimpleXMLElement(<<<XML
<transactionResponse>
    <responseCode>1</responseCode>
    <authCode>AZ2I8P</authCode>
    <avsResultCode>Y</avsResultCode>
    <cvvResultCode />
    <cavvResultCode>2</cavvResultCode>
    <transId>2247334234</transId>
    <refTransID />
    <transHash>CD43D1C3BECA891DFFE44A758EB58F97</transHash>
    <testRequest>0</testRequest>
    <accountNumber>XXXX1111</accountNumber>
    <accountType>Visa</accountType>
    <messages>
        <message>
            <code>1</code>
            <description>This transaction has been approved.</description>
        </message>
    </messages>
</transactionResponse>
XML
        );
        $actual = EcomDev_Utils_Reflection::invokeRestrictedMethod($this->model, '_getResult', array($response));
        $this->assertEquals(1, $actual->getResponseCode());
        $this->assertEquals('1', $actual->getResponseReasonCode());
        $this->assertEquals('This transaction has been approved.', $actual->getResponseReasonText());
        $this->assertEquals('AZ2I8P', $actual->getApprovalCode());
        $this->assertEquals('Y', $actual->getAvsResultCode());
        $this->assertEquals('2247334234', $actual->getTransactionId());
        $this->assertEquals('CD43D1C3BECA891DFFE44A758EB58F97', $actual->getMd5Hash());
        $this->assertEquals('', $actual->getCardCodeResponseCode());
        $this->assertEquals('2', $actual->getCAVVResponseCode());
        $this->assertEquals('XXXX1111', $actual->getAccNumber());
        $this->assertEquals('Visa', $actual->getCardType());
    }

}
