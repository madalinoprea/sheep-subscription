<?php

/**
 * Class Sheep_Subscription_Test_Block_Subscription_Edit_ShippingMethod
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers   Sheep_Subscription_Block_Subscription_Edit_ShippingMethod
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Block_Subscription_Edit_ShippingMethod extends EcomDev_PHPUnit_Test_Case
{
    protected $block;

    protected function setUp()
    {
        parent::setUp();
        $this->block = $this->getBlockMock('sheep_subscription/subscription_edit_shippingMethod', array('toHtml'));
    }

    public function testGetSubscription()
    {
        $this->replaceRegistry('pss_subscription', 'subscription registry');
        $actual = $this->block->getSubscription();
        $this->assertEquals('subscription registry', $actual);
    }

    public function testGetBackUrl()
    {
        $helper = $this->getHelperMock('sheep_subscription', array('getSubscriptionUrl'));
        $helper->expects($this->once())->method('getSubscriptionUrl')->with(100);
        $this->replaceByMock('helper', 'sheep_subscription', $helper);

        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getId'));
        $subscription->expects($this->any())->method('getId')->willReturn(100);

        $block = $this->getBlockMock('sheep_subscription/subscription_edit_shippingMethod', array('getSubscription'));
        $block->expects($this->any())->method('getSubscription')->willReturn($subscription);

        $block->getBackUrl();
    }

    public function testGetSaveShippingMethodUrl()
    {
        $helper = $this->getHelperMock('sheep_subscription', array('getSaveShippingMethodUrl'));
        $helper->expects($this->once())->method('getSaveShippingMethodUrl')->with(100);
        $this->replaceByMock('helper', 'sheep_subscription', $helper);

        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getId'));
        $subscription->expects($this->any())->method('getId')->willReturn(100);

        $block = $this->getBlockMock('sheep_subscription/subscription_edit_shippingMethod', array('getSubscription'));
        $block->expects($this->any())->method('getSubscription')->willReturn($subscription);

        $block->getSaveShippingMethodUrl();
    }

    public function testGetQuote()
    {
        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getQuote'));
        $subscription->expects($this->once())->method('getQuote');

        $block = $this->getBlockMock('sheep_subscription/subscription_edit_shippingMethod', array('getSubscription'));
        $block->expects($this->any())->method('getSubscription')->willReturn($subscription);

        $block->getQuote();
    }

    public function testGetCheckout()
    {
        $this->assertNull($this->block->getCheckout());
    }

}

