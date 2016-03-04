<?php

/**
 * Class Sheep_Subscription_Test_Block_Subscription_View
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, {2015}, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_Block_Subscription_View
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Block_Subscription_View extends EcomDev_PHPUnit_Test_Case
{

    public function testGetSubscription()
    {
        $block = $this->getBlockMock('sheep_subscription/subscription_view', array('toHtml'));
        $this->replaceRegistry('pss_subscription', 'subscription registry');
        $actual = $block->getSubscription();
        $this->assertEquals('subscription registry', $actual);
    }


    public function testGetBackUrl()
    {
        $helperMock = $this->getHelperMock('sheep_subscription', array('getSubscriptionListUrl'));
        $helperMock->expects($this->once())->method('getSubscriptionListUrl');
        $this->replaceByMock('helper', 'sheep_subscription', $helperMock);

        $block = $this->getBlockMock('sheep_subscription/subscription_view', array('toHtml'));
        $block->getBackUrl();
    }


    public function testOrderUrl()
    {
        $block = $this->getBlockMock('sheep_subscription/subscription_view', array('getUrl'));
        $block->expects($this->once())->method('getUrl')->with('sales/order/view', array('order_id' => 101));
        $block->getOrderUrl(101);
    }


    public function testGetOrderStatusUrl()
    {
        $orderConfig = $this->getModelMock('sales/order_config');
        $orderConfig->expects($this->once())->method('getStatusLabel')->with('processing')->willReturn('Processing');
        $this->replaceByMock('singleton', 'sales/order_config', $orderConfig);

        $block = $this->getBlockMock('sheep_subscription/subscription_view', array('toHtml'));
        $actual = $block->getOrderStatusLabel('processing');
        $this->assertEquals('Processing', $actual);
    }


    public function testGetEditShippingAddressUrl()
    {
        $helperMock = $this->getHelperMock('sheep_subscription', array('getEditShippingMethodUrl'));
        $helperMock->expects($this->once())->method('getEditShippingMethodUrl')->with(100);
        $this->replaceByMock('helper', 'sheep_subscription', $helperMock);

        $block = $this->getBlockMock('sheep_subscription/subscription_view', array('getSubscription'));
        $block->expects($this->any())->method('getSubscription')->willReturn(new Varien_Object(array('id' => 100)));
        $block->getEditShippingMethodUrl();
    }


    public function testCanChangeShippingInformation()
    {
        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getId'));
        $subscription->expects($this->any())->method('getId')->willReturn(100);

        $service = $this->getModelMock('sheep_subscription/service', array('canChangeShippingInformation'));
        $service->expects($this->once())->method('canChangeShippingInformation')->with($subscription)->willReturn(true);
        $this->replaceByMock('model', 'sheep_subscription/service', $service);

        $block = $this->getBlockMock('sheep_subscription/subscription_view', array('getSubscription'));
        $block->expects($this->any())->method('getSubscription')->willReturn($subscription);
        $block->canChangeShippingInformation();
    }


    public function testCanChangePaymentInformation()
    {
        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getId'));
        $subscription->expects($this->any())->method('getId')->willReturn(100);

        $service = $this->getModelMock('sheep_subscription/service', array('canChangePaymentInformation'));
        $service->expects($this->once())->method('canChangePaymentInformation')->with($subscription)->willReturn(true);
        $this->replaceByMock('model', 'sheep_subscription/service', $service);

        $block = $this->getBlockMock('sheep_subscription/subscription_view', array('getSubscription'));
        $block->expects($this->any())->method('getSubscription')->willReturn($subscription);
        $block->canChangePaymentInformation();
    }


    public function testGetChangePaymentUrl()
    {
        $helperMock = $this->getHelperMock('sheep_subscription', array('getEditPaymentUrl'));
        $helperMock->expects($this->once())->method('getEditPaymentUrl')->with(100);
        $this->replaceByMock('helper', 'sheep_subscription', $helperMock);

        $block = $this->getBlockMock('sheep_subscription/subscription_view', array('getSubscription'));
        $block->expects($this->any())->method('getSubscription')->willReturn(new Varien_Object(array('id' => 100)));
        $block->getChangePaymentUrl();
    }


    public function testPrepareLayout()
    {
        $paymentInfoMock = $this->getModelMock('payment/info');

        $helperMock = $this->getHelperMock('payment', array('getInfoBlock'));
        $helperMock->expects($this->once())->method('getInfoBlock')->with($paymentInfoMock)->willReturn('payment info block');
        $this->replaceByMock('helper', 'payment', $helperMock);

        $subscriptionMock = $this->getModelMock('sheep_subscription/subscription', array('getPayment'));
        $subscriptionMock->expects($this->any())->method('getPayment')->willReturn($paymentInfoMock);


        /** @var Sheep_Subscription_Block_Subscription_View $block */
        $block = $this->getBlockMock('sheep_subscription/subscription_view', array('getSubscription', 'setChild'));
        $block->expects($this->once())->method('getSubscription')->willReturn($subscriptionMock);
        $block->expects($this->once())->method('setChild')->with('payment_info', 'payment info block');

        EcomDev_Utils_Reflection::invokeRestrictedMethod($block, '_prepareLayout');
    }

}
