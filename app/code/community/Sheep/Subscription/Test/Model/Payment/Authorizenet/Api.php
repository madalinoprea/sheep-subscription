<?php

/**
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_Model_Payment_Authorizenet_Api
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Model_Payment_Authorizenet_Api extends EcomDev_PHPUnit_Test_Case
{
    /** @var Sheep_Subscription_Model_Payment_Authorizenet_Api */
    protected $model;

    protected function setUp()
    {
        parent::setUp();
        $this->model = Mage::getModel('sheep_subscription/payment_authorizenet_api');
    }


    public function testCreateCustomerProfileFromTransaction()
    {
        $request = new SimpleXMLElement('<createCustomerProfileFromTransactionRequest/>');
        $responseXml = new SimpleXMLElement(<<<XML
<response>
    <messages>
        <resultCode>Ok</resultCode>
    </messages>
    <customerProfileId>10010</customerProfileId>
</response>
XML
);

        $model = $this->getModelMock('sheep_subscription/payment_authorizenet_api', array('getRequest', '_callApi'));
        $model->expects($this->once())->method('getRequest')->with('createCustomerProfileFromTransactionRequest')->willReturn($request);
        $model->expects($this->once())->method('_callApi')->with($this->attributeEqualTo('transId', 'A121212'))->willReturn($responseXml);

        $actual = $model->createCustomerProfileFromTransaction('A121212');
        $this->assertEquals('10010', $actual);
    }


    public function testCreateCustomerProfileFromTransactionDuplicate()
    {
        $request = new SimpleXMLElement('<createCustomerProfileFromTransactionRequest/>');
        $responseXml = new SimpleXMLElement(<<<XML
<response>
    <messages>
        <resultCode>Error</resultCode>
        <message>
            <code>E00039</code>
            <text>A duplicate record with ID 10010 already exists</text>
        </message>
    </messages>
    <customerProfileId>10010</customerProfileId>
</response>
XML
        );

        $model = $this->getModelMock('sheep_subscription/payment_authorizenet_api', array('getRequest', '_callApi'));
        $model->expects($this->once())->method('getRequest')->with('createCustomerProfileFromTransactionRequest')->willReturn($request);
        $model->expects($this->once())->method('_callApi')->with($this->attributeEqualTo('transId', 'A121212'))->willReturn($responseXml);

        $actual = $model->createCustomerProfileFromTransaction('A121212');
        $this->assertEquals('10010', $actual);
    }


    public function testCreateCustomerProfileFromTransactionWithError()
    {
        $request = new SimpleXMLElement('<createCustomerProfileFromTransactionRequest/>');
        $responseXml = new SimpleXMLElement(<<<XML
<response>
    <messages>
        <resultCode>Error</resultCode>
        <message>
            <code>E00040</code>
            <text>A duplicate record with ID 10010 already exists</text>
        </message>
    </messages>
    <customerProfileId>10010</customerProfileId>
</response>
XML
        );

        $model = $this->getModelMock('sheep_subscription/payment_authorizenet_api', array('getRequest', '_callApi'));
        $model->expects($this->once())->method('getRequest')->with('createCustomerProfileFromTransactionRequest')->willReturn($request);
        $model->expects($this->once())->method('_callApi')->with($this->attributeEqualTo('transId', 'A121212'))->willReturn($responseXml);

        $actual = $model->createCustomerProfileFromTransaction('A121212');
        $this->assertEquals('', $actual);
    }


    public function testGetCustomerProfile()
    {
        $request = new SimpleXMLElement('<getCustomerProfileRequest/>');
        $responseXml = new SimpleXMLElement(<<<XML
<?xml version="1.0" encoding="utf-8"?>
<getCustomerProfileResponse xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
    <messages>
        <resultCode>Ok</resultCode>
        <message><code>I00001</code><text>Successful.</text></message>
    </messages>
    <profile>
        <merchantCustomerId>140</merchantCustomerId>
        <email>mario@moprea.ro</email>
        <customerProfileId>38607755</customerProfileId>
        <paymentProfiles>
            <billTo><firstName>Mario</firstName><lastName>O</lastName><company>Pirate Sheep</company><address>44th st</address><city>New York</city><state>New York</state><zip>120230</zip><country>US</country><phoneNumber>111111111</phoneNumber></billTo>
            <customerPaymentProfileId>35112901</customerPaymentProfileId>
            <payment><creditCard><cardNumber>XXXX1111</cardNumber><expirationDate>XXXX</expirationDate></creditCard></payment>
        </paymentProfiles>
        <shipToList>
            <firstName>Mario</firstName><lastName>O</lastName><company>Pirate Sheep</company><address>44th st</address><city>New York</city><state>New York</state><zip>120230</zip><country>US</country><phoneNumber>111111111</phoneNumber><customerAddressId>36695539</customerAddressId>
        </shipToList>
    </profile>
</getCustomerProfileResponse>
XML
        );

        $model = $this->getModelMock('sheep_subscription/payment_authorizenet_api', array('getRequest', '_callApi'));
        $model->expects($this->once())->method('getRequest')->with('getCustomerProfileRequest')->willReturn($request);
        $model->expects($this->once())->method('_callApi')->with($this->attributeEqualTo('customerProfileId', '10010'))->willReturn($responseXml);

        $actual = $model->getCustomerProfile(10010);
        $this->assertNotNull($actual);
        $this->assertInstanceOf('SimpleXMLElement', $actual);
        $this->assertEquals('140', (string)$actual->merchantCustomerId);
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Not found.
     */
    public function testGetCustomerProfileWithError()
    {
        $request = new SimpleXMLElement('<getCustomerProfileRequest/>');
        $responseXml = new SimpleXMLElement(<<<XML
<?xml version="1.0" encoding="utf-8"?>
<getCustomerProfileResponse xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
    <messages>
        <resultCode>Error</resultCode>
        <message><code>I00001</code><text>Not found.</text></message>
    </messages>
</getCustomerProfileResponse>
XML
        );

        $model = $this->getModelMock('sheep_subscription/payment_authorizenet_api', array('getRequest', '_callApi'));
        $model->expects($this->once())->method('getRequest')->with('getCustomerProfileRequest')->willReturn($request);
        $model->expects($this->once())->method('_callApi')->with($this->attributeEqualTo('customerProfileId', '10010'))->willReturn($responseXml);

        $actual = $model->getCustomerProfile(10010);
        $this->assertNotNull($actual);
        $this->assertInstanceOf('SimpleXMLElement', $actual);
        $this->assertEquals('140', (string)$actual->merchantCustomerId);
    }


    public function testGetPaymentProfileId()
    {
        $responseXml = new SimpleXMLElement(<<<XML
<profile>
    <merchantCustomerId>140</merchantCustomerId>
    <email>mario@moprea.ro</email>
    <customerProfileId>38607755</customerProfileId>
    <paymentProfiles>
        <billTo><firstName>Mario</firstName><lastName>O</lastName><company>Pirate Sheep</company><address>44th st</address><city>New York</city><state>New York</state><zip>120230</zip><country>US</country><phoneNumber>111111111</phoneNumber></billTo>
        <customerPaymentProfileId>35112901</customerPaymentProfileId>
        <payment><creditCard><cardNumber>XXXX1111</cardNumber><expirationDate>XXXX</expirationDate></creditCard></payment>
    </paymentProfiles>
    <shipToList>
        <firstName>Mario</firstName><lastName>O</lastName><company>Pirate Sheep</company><address>44th st</address><city>New York</city><state>New York</state><zip>120230</zip><country>US</country><phoneNumber>111111111</phoneNumber><customerAddressId>36695539</customerAddressId>
    </shipToList>
</profile>
XML
        );

        $model = $this->getModelMock('sheep_subscription/payment_authorizenet_api', array('getCustomerProfile'));
        $model->expects($this->once())->method('getCustomerProfile')->with(10020)->willReturn($responseXml);

        $actual = $model->getPaymentProfileId(10020);
        $this->assertNotNull($actual);
        $this->assertEquals('35112901', $actual);
    }


    public function testGetChargeRequest()
    {
        $aimRequest = Mage::getModel('paygate/authorizenet_request');
        $aimRequest->setData(array(
            'x_type' => 'AUTH_CAPTURE',
            'x_amount' => 201.55,
            'x_invoice_num' => 100001,
            'x_tax' => 23,
            'x_freight' => 16,
            'x_po_num' => 1001,
            'x_cust_id' => 10,
            'x_email' => 'mario@moprea.ro',
            'x_ship_to_first_name' => 'Mario',
            'x_ship_to_last_name' => 'O',
            'x_ship_to_company' => 'Pirate Sheep',
            'x_ship_to_address' => '44th st',
            'x_ship_to_city' => 'New York',
            'x_ship_to_state' => 'NY',
            'x_ship_to_zip' => '10001',
            'x_ship_to_country' => 'US',
            'x_customer_ip' => '127.0.0.1',
            'x_email_customer' => 1,
        ));

        $actual = $this->model->getChargeRequest(101, 20000, $aimRequest);
        $this->assertNotNull($actual);
        $this->assertInstanceOf('SimpleXMlElement', $actual);
        $this->assertEquals('createTransactionRequest', $actual->getName())
        ;
        $this->assertEquals('authCaptureTransaction', $actual->transactionRequest->transactionType);
        $this->assertEquals('201.55', $actual->transactionRequest->amount);

        $this->assertEquals('101', $actual->transactionRequest->profile->customerProfileId);
        $this->assertEquals('20000', $actual->transactionRequest->profile->paymentProfile->paymentProfileId);

        $this->assertEquals('100001', $actual->transactionRequest->order->invoiceNumber);

        $this->assertEquals('Tax', $actual->transactionRequest->tax->name);
        $this->assertEquals('23', $actual->transactionRequest->tax->amount);

        $this->assertEquals('', $actual->transactionRequest->shipping->name);
        $this->assertEquals('16', $actual->transactionRequest->shipping->amount);

        $this->assertEquals('1001', $actual->transactionRequest->poNumber);

        $this->assertEquals('10', $actual->transactionRequest->customer->id);
        $this->assertEquals('mario@moprea.ro', $actual->transactionRequest->customer->email);

        $this->assertEquals('Mario', $actual->transactionRequest->shipTo->firstName);
        $this->assertEquals('O', $actual->transactionRequest->shipTo->lastName);
        $this->assertEquals('Pirate Sheep', $actual->transactionRequest->shipTo->company);
        $this->assertEquals('44th st', $actual->transactionRequest->shipTo->address);
        $this->assertEquals('New York', $actual->transactionRequest->shipTo->city);
        $this->assertEquals('NY', $actual->transactionRequest->shipTo->state);
        $this->assertEquals('10001', $actual->transactionRequest->shipTo->zip);
        $this->assertEquals('US', $actual->transactionRequest->shipTo->country);

        $this->assertEquals('127.0.0.1', $actual->transactionRequest->customerIP);

        $this->assertEquals('emailCustomer', $actual->transactionRequest->transactionSettings->setting->settingName);
        $this->assertEquals('1', $actual->transactionRequest->transactionSettings->setting->settingValue);
    }


    public function testChargeCustomerProfile()
    {
        $request = Mage::getModel('paygate/authorizenet_request');
        $transactionRequest = new SimpleXMLElement('<createTransactionRequest/>');
        $transactionResponseXml = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<createTransactionResponse xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
    <messages>
        <resultCode>Ok</resultCode>
        <message><code>I00001</code><text>Successful.</text></message>
    </messages>
    <transactionResponse>
        <responseCode>1</responseCode>
        <authCode>AZ2I8P</authCode>
        <avsResultCode>Y</avsResultCode>
        <cvvResultCode />
        <cavvResultCode>2</cavvResultCode>
        <transId>2247334234</transId>
    </transactionResponse>
</createTransactionResponse>
XML;
        $transactionResponse = new SimpleXMLElement($transactionResponseXml);

        $model = $this->getModelMock('sheep_subscription/payment_authorizenet_api', array('getPaymentProfileId', 'getChargeRequest', '_callApi'));
        $model->expects($this->once())->method('getPaymentProfileId')->with(10020)->willReturn(1000100);
        $model->expects($this->once())->method('getChargeRequest')->with(10020, 1000100, $request)->willReturn($transactionRequest);
        $model->expects($this->once())->method('_callApi')->with($transactionRequest)->willReturn($transactionResponse);

        $actual = $model->chargeCustomerProfile(10020, $request);
        $this->assertNotNull($actual);
        $this->assertInstanceOf('SimpleXMLElement', $actual);
        $this->assertEquals('transactionResponse', $actual->getName());
    }


    public function testCallApi()
    {
        $request = new SimpleXMLElement('<request/>');
        $response = new Zend_Http_Response(200, array(), '<response><code>1</code></response>');

        $client = $this->getMock('Varien_Http_Client', array('setRawData', 'request'));
        $client->expects($this->once())->method('setRawData')->with();
        $client->expects($this->once())->method('request')->with(Zend_Http_Client::POST)->willReturn($response);

        $model = $this->getModelMock('sheep_subscription/payment_authorizenet_api', array('getClient'));
        $model->expects($this->any())->method('getClient')->willReturn($client);

        $actual = EcomDev_Utils_Reflection::invokeRestrictedMethod($model, '_callApi', array($request));
        $this->assertNotNull($actual);
        $this->assertInstanceOf('SimpleXMLElement', $actual);
        $this->assertEquals('1', (string)$actual->code);
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Invalid response code from API: 503
     */
    public function testCallApiWithHttpError()
    {
        $request = new SimpleXMLElement('<request/>');
        $response = new Zend_Http_Response(503, array(), '');

        $client = $this->getMock('Varien_Http_Client', array('setRawData', 'request'));
        $client->expects($this->once())->method('setRawData')->with();
        $client->expects($this->once())->method('request')->with(Zend_Http_Client::POST)->willReturn($response);

        $model = $this->getModelMock('sheep_subscription/payment_authorizenet_api', array('getClient'));
        $model->expects($this->any())->method('getClient')->willReturn($client);

        EcomDev_Utils_Reflection::invokeRestrictedMethod($model, '_callApi', array($request));
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Unable to parse API response
     */
    public function testCallApiWithXmlError()
    {
        $request = new SimpleXMLElement('<request>data</request>');
        $response = new Zend_Http_Response(200, array(), '<invalid_xml>ok&</invalid_xml>');

        $client = $this->getMock('Varien_Http_Client', array('setRawData', 'request'));
        $client->expects($this->once())->method('setRawData')->with($this->stringContains('data</request>'));
        $client->expects($this->once())->method('request')->with(Zend_Http_Client::POST)->willReturn($response);

        $model = $this->getModelMock('sheep_subscription/payment_authorizenet_api', array('getClient'));
        $model->expects($this->any())->method('getClient')->willReturn($client);

        EcomDev_Utils_Reflection::invokeRestrictedMethod($model, '_callApi', array($request));
    }


    public function testGetClient()
    {

        $this->model->setApiEndPoint('https://www.moprea.ro');
        $actual = $this->model->getClient();

        $this->assertNotNull($actual);
        $this->assertInstanceOf('Varien_Http_Client', $actual);
        $this->assertEquals('text/xml', $actual->getHeader('Content-Type'));
    }


    public function testGetRequest()
    {
        $this->model->setLogin('user');
        $this->model->setTransKey('password');

        $actual = $this->model->getRequest('sampleMethod');

        $this->assertNotNull($actual);
        $this->assertInstanceOf('SimpleXMLElement', $actual);
        $this->assertEquals('sampleMethod', $actual->getName());
        $this->assertEquals('user', $actual->merchantAuthentication->name);
        $this->assertEquals('password', $actual->merchantAuthentication->transactionKey);
    }


    public function testGetApiTransactionType()
    {
        $actual = $this->model->getApiTransactionType('AUTH_CAPTURE');
        $this->assertEquals('authCaptureTransaction', $actual);

        $actual = $this->model->getApiTransactionType('AUTH_ONLY');
        $this->assertEquals('authOnlyTransaction', $actual);

        $actual = $this->model->getApiTransactionType('CAPTURE_ONLY');
        $this->assertEquals('captureOnlyTransaction', $actual);

        $actual = $this->model->getApiTransactionType('unsupported');
        $this->assertEquals('unsupported', $actual);

    }


    public function testParseIdFromErrorMessage()
    {
        $errorMessage = 'A duplicate record with ID 12345 already exists';

        $actual = EcomDev_Utils_Reflection::invokeRestrictedMethod($this->model, '_parseIdFromErrorMessage', array($errorMessage));
        $this->assertEquals('12345', $actual);
    }
}
