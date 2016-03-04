<?php

/**
 * Class Sheep_Subscription_Model_Payment_Authorizenet_Api
 *
 * @category Sheep
 * @package Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 *
 * Documentation references:
 *      - Requests for api methods is described by https://api.authorize.net/xml/v1/schema/AnetApiSchema.xsd
 *      - AIM request and responses are described by http://www.authorize.net/content/dam/authorize/documents/AIM_guide.pdf
 */
class Sheep_Subscription_Model_Payment_Authorizenet_Api
{
    protected $_login;
    protected $_transKey;
    protected $_apiEndPoint;
    protected $_client;


    /**
     * Creates a customer profile based on specified transaction id
     *
     * @see http://developer.authorize.net/api/reference/index.html#customer-profiles-create-a-customer-profile-from-a-transaction
     * @param string $transactionId
     * @return string
     * @throws Exception
     */
    public function createCustomerProfileFromTransaction($transactionId)
    {
        if (!$transactionId) {
            throw new Exception('Invalid transaction id');
        }

        $request = $this->getRequest('createCustomerProfileFromTransactionRequest');
        $request->transId = $transactionId;

        $responseXml = $this->_callApi($request);

        // New customer profile created successfully
        if ($responseXml->messages->resultCode == 'Ok') {
            return (string)$responseXml->customerProfileId;
        }

        // We might have a customer profile already created
        if ($responseXml->messages->resultCode == 'Error' && $responseXml->messages->message->code == 'E00039') {
            // Try to parse the id
            return $this->_parseIdFromErrorMessage($responseXml->messages->message->text);
        }

        return '';
    }


    /**
     * Returns customer profile information specified by its id
     *
     * @see http://developer.authorize.net/api/reference/?dev-menu-search-cta=Submit&dev-menu-search-textfield=transactionType#payment-transactions-charge-a-customer-profile
     * @param string $customerProfileId
     * @return SimpleXMLElement
     * @throws Exception
     */
    public function getCustomerProfile($customerProfileId)
    {
        $request = $this->getRequest('getCustomerProfileRequest');
        $request->customerProfileId = $customerProfileId;

        $response = $this->_callApi($request);

        if ($response->messages->resultCode != 'Ok') {
            throw new Exception($response->messages->message->text);
        }

        return $response->profile;
    }


    /**
     * Return payment profile id registered on specified customer profiled id
     *
     * @param string $customerProfileId
     * @return string
     */
    public function getPaymentProfileId($customerProfileId)
    {
        $customerProfile = $this->getCustomerProfile($customerProfileId);
        return (string)$customerProfile->paymentProfiles->customerPaymentProfileId;
    }

    /**
     * Returns a createTransactionRequest filled in with information from AIM request
     *
     * @param string $customerProfileId
     * @param string $paymentProfileId
     * @param Mage_Paygate_Model_Authorizenet_Request $aimRequest
     * @return SimpleXMLElement
     */
    public function getChargeRequest($customerProfileId, $paymentProfileId, Mage_Paygate_Model_Authorizenet_Request $aimRequest)
    {
        $request = $this->getRequest('createTransactionRequest');

        $transaction = $request->addChild('transactionRequest');
        $transaction->transactionType = $this->getApiTransactionType($aimRequest->getXType());
        $transaction->amount = $aimRequest->getXAmount();

        $profile = $transaction->addChild('profile');
        $profile->customerProfileId = $customerProfileId;
        $paymentProfile = $profile->addChild('paymentProfile');
        $paymentProfile->paymentProfileId = $paymentProfileId;

        $orderInfo = $transaction->addChild('order');
        $orderInfo->invoiceNumber = $aimRequest->getXInvoiceNum();
        $orderInfo->description = '';


        $tax = $transaction->addChild('tax');
        $tax->amount = $aimRequest->getXTax();
        $tax->name = 'Tax';
        $tax->description = '';

        // shipping details
        $shipping = $transaction->addChild('shipping');
        $shipping->amount = $aimRequest->getXFreight();
        $shipping->name = '';
        $shipping->description = '';

        $transaction->addChild('poNumber', $aimRequest->getXPoNum());

        $customer = $transaction->addChild('customer');
        $customer->id = $aimRequest->getXCustId();
        $customer->email = $aimRequest->getXEmail();

        if ($aimRequest->getXShipToFirstName()) {
            $shipTo = $transaction->addChild('shipTo');
            $shipTo->firstName = $aimRequest->getXShipToFirstName();
            $shipTo->lastName = $aimRequest->getXShipToLastName();
            $shipTo->company = $aimRequest->getXShipToCompany();
            $shipTo->address = $aimRequest->getXShipToAddress();
            $shipTo->city = $aimRequest->getXShipToCity();
            $shipTo->state = $aimRequest->getXShipToState();
            $shipTo->zip = $aimRequest->getXShipToZip();
            $shipTo->country = $aimRequest->getXShipToCountry();

        }

        $transaction->addChild('customerIP', $aimRequest->getXCustomerIp());

        $settings = $transaction->addChild('transactionSettings');
        $settings->setting = $settings->addChild('setting');
        $settings->setting->settingName = 'emailCustomer';
        $settings->setting->settingValue = $aimRequest->getXEmailCustomer();

        return $request;
    }


    /**
     * Charges a customer profile id based on transaction information provided as AIM request.
     *
     * AIM requests are build by @see Mage_Paygate_Model_Authorizenet::_buildRequest
     *
     * @see http://developer.authorize.net/api/reference/?dev-menu-search-cta=Submit&dev-menu-search-textfield=transactionType#payment-transactions-charge-a-customer-profile
     *
     * @param $customerProfileId
     * @param Mage_Paygate_Model_Authorizenet_Request $aimRequest
     * @return SimpleXMLElement[]
     * @throws Exception
     */
    public function chargeCustomerProfile($customerProfileId, Mage_Paygate_Model_Authorizenet_Request $aimRequest)
    {
        $paymentProfileId = $this->getPaymentProfileId($customerProfileId);
        $request = $this->getChargeRequest($customerProfileId, $paymentProfileId, $aimRequest);

        $response = $this->_callApi($request);

        if ($response->messages->resultCode != 'Ok') {
            throw new Exception($response->messages->message->text);
        }

        return $response->transactionResponse;
    }


    /**
     * Calls Authorize.Net Api using specified request and returns an XMLElement representing the response
     *
     * Also adds xlmns on request element and strips it from response to bypass SimpleXML warning related
     * to relative xmlns.
     *
     * @param SimpleXMLElement $request
     * @return SimpleXMLElement
     * @throws Exception
     */
    protected function _callApi(SimpleXMLElement $request)
    {
        // xmlns attribute is required
        $request->addAttribute('xmlns', 'AnetApi/xml/v1/schema/AnetApiSchema.xsd');

        $client = $this->getClient();
        $client->setRawData($request->asXML());

        /** @var Zend_Http_Response $response */
        $response = $client->request(Zend_Http_Client::POST);

        // Handle response
        if ($response->getStatus() != 200) {
            throw new Exception('Invalid response code from API: ' . $response->getStatus());
        }

        try {
            // Strip xmlns from response, see https://community.developer.authorize.net/t5/Integration-and-Testing/Error-E00045-PHP-SDK-Goal-authorizeNetCIM-gt/td-p/13806
            $responseBody = str_replace(' xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd"', '', $response->getBody());
            $responseXml = new SimpleXMLElement($responseBody);
        } catch (Exception $e) {
            Mage::logException($e);
            throw new Exception('Unable to parse API response');
        }

        return $responseXml;
    }


    /**
     * Returns http client based on configured API endpoint
     *
     * @return Varien_Http_Client
     * @throws Exception
     * @throws Zend_Http_Client_Exception
     */
    public function getClient()
    {
        if ($this->_client === null) {

            if (!$this->_apiEndPoint) {
                throw new Exception('Api endpoint is not configured.');
            }

            $this->_client = new Varien_Http_Client($this->_apiEndPoint);
            $this->_client->setHeaders(array('Content-Type: text/xml'));
            $this->_client->setConfig(array('timeout' => 45));
        }

        return $this->_client;
    }


    /**
     * Creates a XML representing specified request with authentication.
     *
     * @param $requestMethod
     * @return SimpleXMLElement
     */
    public function getRequest($requestMethod)
    {
        $request = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"utf-8\"?><{$requestMethod}/>");
        $authentication = $request->addChild('merchantAuthentication');
        $authentication->name = $this->_login;
        $authentication->transactionKey = $this->_transKey;

        return $request;
    }


    /**
     * Transforms Mage_Paygate_Model_Authorizenet (AIM) transaction types into our API transaction type
     *
     * @param string $transactionType
     * @return string
     */
    public function getApiTransactionType($transactionType)
    {
        $mapping = array(
            Mage_Paygate_Model_Authorizenet::REQUEST_TYPE_AUTH_CAPTURE => 'authCaptureTransaction',
            Mage_Paygate_Model_Authorizenet::REQUEST_TYPE_AUTH_ONLY => 'authOnlyTransaction',
            Mage_Paygate_Model_Authorizenet::REQUEST_TYPE_CAPTURE_ONLY => 'captureOnlyTransaction',
        );

        return array_key_exists($transactionType, $mapping) ? $mapping[$transactionType] : $transactionType;
    }


    /**
     * Tries to identify a profile id from an error message by stripping all non numeric characters.
     *
     * @param string $errorMessage
     * @return mixed
     */
    protected function _parseIdFromErrorMessage($errorMessage)
    {
        return filter_var($errorMessage, FILTER_SANITIZE_NUMBER_INT);
    }


    /**
     * @param mixed $login
     */
    public function setLogin($login)
    {
        $this->_login = $login;
    }


    /**
     * @param mixed $transKey
     */
    public function setTransKey($transKey)
    {
        $this->_transKey = $transKey;
    }


    /**
     * @param mixed $apiEndPoint
     */
    public function setApiEndPoint($apiEndPoint)
    {
        $this->_apiEndPoint = $apiEndPoint;
        $this->_client = null;
    }
}
