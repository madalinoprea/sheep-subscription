<?php

/**
 * Class Sheep_Subscription_Test_Block_Adminhtml_Subscription_View_Tabs_Info
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, {2015}, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_Block_Adminhtml_Subscription_View_Tabs_Info
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Block_Adminhtml_Subscription_View_Tabs_Info extends EcomDev_PHPUnit_Test_Case
{
    protected $block;

    protected function setUp()
    {
        parent::setUp();

        $this->block = $this->getBlockMock('sheep_subscription/adminhtml_subscription_view_tabs_info',
            array('getUrl', 'getLayout', '_preparePage', '_afterLoadCollection', 'getChildHtml'));

        $layoutMock = $this->getModelMock('core/layout', array('createBlock'));
        $layoutMock->expects($this->any())->method('createBlock')->willReturn($this->getBlockMock('core/text', array('toHtml')));
        $this->block->expects($this->any())->method('getLayout')->willReturn($layoutMock);
    }


    public function testGetSubscription()
    {
        $block = $this->getBlockMock('sheep_subscription/adminhtml_subscription_view_tabs_info', array('toHtml'));
        $this->replaceRegistry('pss_subscription', 'subscription registry');
        $actual = $subscription = $block->getSubscription();
        $this->assertEquals('subscription registry', $actual);
    }

    public function testPrepareLayout()
    {
        $paymentHelper = $this->getHelperMock('payment', array('getInfoBlock'));
        $paymentHelper->expects($this->once())->method('getInfoBlock')->willReturn($this->getBlockMock('core/text'));
        $this->replaceByMock('helper', 'payment', $paymentHelper);

        $this->replaceRegistry('pss_subscription', new Varien_Object(array('payment' => $this->getModelMock('payment/info'))));
        EcomDev_Utils_Reflection::invokeRestrictedMethod($this->block, '_prepareLayout');

        $this->assertNotFalse($this->block->getChild('payment_info'));
        $this->assertNotFalse($this->block->getChild('subscription_items'));
        $this->assertNotFalse($this->block->getChild('subscription_totals'));
    }

    public function testGetSubscriptionItems()
    {
        $this->block->expects($this->once())->method('getChildHtml')->with('subscription_items');
        $this->block->getSubscriptionItems();
    }

    public function testGetSubscriptionTotals()
    {
        $this->block->expects($this->once())->method('getChildHtml')->with('subscription_totals');
        $this->block->getSubscriptionTotals();
    }
}
