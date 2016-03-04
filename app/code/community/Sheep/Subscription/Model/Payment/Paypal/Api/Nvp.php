<?php

/**
 * Class Sheep_Subscription_Model_Payment_Paypal_Api_Nvp
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Model_Payment_Paypal_Api_Nvp extends Mage_Paypal_Model_Api_Nvp
{

    /**
     * Extends base method to allow to specify extra parameters for DoReferenceTransaction method
     *
     * @param array $defaultRequest
     * @throws Exception
     */
    public function callDoReferenceTransaction($defaultRequest = array())
    {
        $request = $this->_exportToRequest($this->_doReferenceTransactionRequest);
        $this->_exportLineItems($request);

        // Overwrite or add additional request parameters
        $request = array_merge($request, $defaultRequest);

        $response = $this->call('DoReferenceTransaction', $request);
        $this->_importFromResponse($this->_doReferenceTransactionResponse, $response);
    }

}
