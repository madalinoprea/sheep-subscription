<?php

/**
 * Class Sheep_Subscription_Test_Model_Payment_Paypal_Api_Nvp
 *
 * @category Sheep
 * @package  Sheep_$
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers   Sheep_Subscription_Model_Payment_Paypal_Api_Nvp
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Model_Payment_Paypal_Api_Nvp extends EcomDev_PHPUnit_Test_Case
{
    /** @var Sheep_Subscription_Model_Payment_Paypal_Api_Nvp */
    protected $model;

    protected function setUp()
    {
        parent::setUp();
        $this->model = $this->getModelMock('sheep_subscription/payment_paypal_api_nvp', array('_exportToRequest', '_exportLineItems', 'call', '_importFromResponse'));
    }

    public function testCallDoReferenceTransaction()
    {
        $defaultRequests = array('b' => 2, 'c' => 3);
        $request = array('a' => 1);
        $completeRequest = array_merge($request, $defaultRequests);

        $this->model->expects($this->once())->method('_exportToRequest')->willReturn($request);
        $this->model->expects($this->once())->method('_exportLineItems');
        $this->model->expects($this->once())->method('call')
            ->with('DoReferenceTransaction', $completeRequest)
            ->willReturn(array());
        $this->model->expects($this->once())->method('_importFromResponse');

        $this->model->callDoReferenceTransaction($defaultRequests);
    }

}
