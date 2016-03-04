<?php

/**
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_Model_Payment_Authorizenet
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Model_Payment_Authorizenet extends EcomDev_PHPUnit_Test_Case
{
    /** @var Sheep_Subscription_Model_Payment_Authorizenet */
    protected $model;

    protected function setUp()
    {
        $this->model = Mage::getModel('sheep_subscription/payment_authorizenet');
    }


    public function testGetApi()
   {
       $payment = $this->getModelMock('paygate/authorizenet', array('getExtendedApi'));
       $payment->expects($this->once())->method('getExtendedApi')->with(10);

       $order = $this->getModelMock('sales/order', array('getStoreId'));
       $order->expects($this->once())->method('getStoreId')->willReturn(10);

       $model = $this->getModelMock('sheep_subscription/payment_authorizenet', array('validate'));
       $model->setPayment($payment);
       $model->getApi($order);
   }


    public function testGetCard()
    {
        $paymentInstance = $this->getModelMock('paygate/authorizenet', array('getCardsStorage'));
        $paymentInstance->expects($this->any())->method('getCardsStorage')->willReturn(
            new Varien_Object(array('cards' => array(
                new Varien_Object(array('id' => 1)),
                new Varien_Object(array('id' => 2))
            )))
        );

        $paymentInfo = $this->getModelMock('sales/order_payment', array('getMethodInstance'));
        $paymentInfo->expects($this->any())->method('getMethodInstance')->willReturn($paymentInstance);

        $order = $this->getModelMock('sales/order', array('getPayment'));
        $order->expects($this->any())->method('getPayment')->willReturn($paymentInfo);

        $actual = $this->model->getCard($order);
        $this->assertNotNull($actual);
        $this->assertEquals(1, $actual->getId());
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Unable to find
     */
    public function testGetCardWithError()
    {
        $paymentInstance = $this->getModelMock('paygate/authorizenet', array('getCardsStorage'));
        $paymentInstance->expects($this->any())->method('getCardsStorage')->willReturn(
            new Varien_Object(array('cards' => array()))
        );

        $paymentInfo = $this->getModelMock('sales/order_payment', array('getMethodInstance'));
        $paymentInfo->expects($this->any())->method('getMethodInstance')->willReturn($paymentInstance);

        $order = $this->getModelMock('sales/order', array('getPayment'));
        $order->expects($this->any())->method('getPayment')->willReturn($paymentInfo);

        $this->model->getCard($order);
    }


    public function testGetTransactionId()
    {
        $order = $this->getModelMock('sales/order', array('load'));

        $model = $this->getModelMock('sheep_subscription/payment_authorizenet', array('getCard'));
        $model->expects($this->once())->method('getCard')->with($order)->willReturn(
            new Varien_Object(array('last_trans_id' => 2000))
        );

        $actual = $model->getTransactionId($order);
        $this->assertEquals(2000, $actual);
    }


    public function testOnCreateSubscription()
    {
        $api = $this->getModelMock('sheep_subscription/payment_authorizenet_api', array('createCustomerProfileFromTransaction'));
        $api->expects($this->once())->method('createCustomerProfileFromTransaction')->with(2000)->willReturn(10000);

        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getId', 'getStartDate'));
        $subscription->expects($this->any())->method('getId')->willReturn(30);
        $subscription->expects($this->any())->method('getStartDate')->willReturn('2015-12-23');

        $subscriptionPayment = $this->getModelMock('sheep_subscription/payment', array('setInfo', 'setSubscriptionId', 'setExpirationDate', 'save'));
        $subscriptionPayment->expects($this->once())->method('setInfo')->with(array('customer_profile_id' => 10000));
        $subscriptionPayment->expects($this->once())->method('setSubscriptionId')->with(30);
        $subscriptionPayment->expects($this->once())->method('setExpirationDate')->with('2016-02-29');
        $subscriptionPayment->expects($this->once())->method('save');
        $this->replaceByMock('model', 'sheep_subscription/payment', $subscriptionPayment);

        $order = $this->getModelMock('sales/order', array('load'));

        // We expect to have a saved renewal that is created by our helper
        $renewal = $this->getModelMock('sheep_subscription/renewal', array('save'));
        $renewal->expects($this->once())->method('save');

        $helper = $this->getHelperMock('sheep_subscription/renewal', array('getRenewal'));
        $helper->expects($this->once())->method('getRenewal')->with($subscription, '2015-12-23')->willReturn($renewal);
        $this->replaceByMock('helper', 'sheep_subscription/renewal', $helper);

        $model = $this->getModelMock('sheep_subscription/payment_authorizenet', array('getTransactionId', 'getApi', 'getCard'));
        $model->expects($this->any())->method('getTransactionId')->willReturn(2000);
        $model->expects($this->any())->method('getApi')->willReturn($api);
        $model->expects($this->any())->method('getCard')->willReturn(
            new Varien_Object(array('cc_exp_month' => '2', 'cc_exp_year' => 2016))
        );

        $model->onCreateSubscription($subscription, $order);
    }
}
