<?php

/**
 * Class Sheep_Subscription_Test_Model_Payment_Paypal_Express
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers   Sheep_Subscription_Model_Payment_Paypal_Express
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Model_Payment_Paypal_Express extends EcomDev_PHPUnit_Test_Case
{
    /** @var Sheep_Subscription_Model_Payment_Paypal_Express $model */
    protected $model;

    protected function setUp()
    {
        parent::setUp();
        $this->model = Mage::getModel('sheep_subscription/payment_paypal_express');
    }

    public function testType()
    {
        $this->assertInstanceOf('Sheep_Subscription_Model_Payment_Paypal_Express', $this->model);
        $this->assertFalse($this->model->isGatewayManaged());
    }

    public function testOnCreateSubscription()
    {
        $renewal = $this->getModelMock('sheep_subscription/renewal', array('save'));
        $renewal->expects($this->once())->method('save');

        $renewalHelper = $this->getHelperMock('sheep_subscription/renewal', array('getRenewal'));
        $renewalHelper->expects($this->once())->method('getRenewal')->willReturn($renewal);
        $this->replaceByMock('helper', 'sheep_subscription/renewal', $renewalHelper);

        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getId'));
        $subscription->expects($this->once())->method('getId')->willReturn(200);

        $orderPayment = $this->getModelMock('sales/order_payment', array('getLastTransId', 'getAdditionalInformation'));
        $orderPayment->expects($this->once())->method('getLastTransId')->willReturn(10001001);

        $order = $this->getModelMock('sales/order', array('getPayment'));
        $order->expects($this->once())->method('getPayment')->willReturn($orderPayment);

        // We expect to have a subscription payment info created
        $payment = $this->getModelMock('sheep_subscription/payment', array('setInfo', 'setSubscriptionId', 'save'));
        $payment->expects($this->once())->method('setInfo')->with();
        $payment->expects($this->once())->method('setSubscriptionId')->with(200);
        $payment->expects($this->once())->method('save');
        $this->replaceByMock('model', 'sheep_subscription/payment', $payment);


        $this->model->onCreateSubscription($subscription, $order);
    }

}
