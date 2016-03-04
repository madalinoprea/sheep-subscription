<?php

/**
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers   Sheep_Subscription_Model_Payment_Local
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Model_Payment_Local extends EcomDev_PHPUnit_Test_Case
{
    /** @var Sheep_Subscription_Model_Payment_Local $model */
    protected $model;


    protected function setUp()
    {
        parent::setUp();
        $this->model = Mage::getModel('sheep_subscription/payment_local');
    }


    public function testType()
    {
        $this->assertInstanceOf('Sheep_Subscription_Model_Payment_Local', $this->model);
        $this->assertFalse($this->model->isGatewayManaged());
    }


    public function testOnCreateSubscription()
    {
        $subscription = $this->getModelMock('sheep_subscription/subscription');
        $order = $this->getModelMock('sales/order');

        $renewal = $this->getModelMock('sheep_subscription/renewal', array('save'));
        $renewal->expects($this->once())->method('save');

        $helper = $this->getHelperMock('sheep_subscription/renewal', array('getRenewal'));
        $helper->expects($this->once())->method('getRenewal')->with($subscription)->willReturn($renewal);
        $this->replaceByMock('helper', 'sheep_subscription/renewal', $helper);

        $this->model->onCreateSubscription($subscription, $order);
    }

}
