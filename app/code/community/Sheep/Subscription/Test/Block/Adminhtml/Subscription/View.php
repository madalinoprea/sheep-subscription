<?php

/**
 * Class Sheep_Subscription_Test_Block_Adminhtml_Subscription_View
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, {2015}, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_Block_Adminhtml_Subscription_View
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Block_Adminhtml_Subscription_View extends EcomDev_PHPUnit_Test_Case
{
    /** @var Sheep_Subscription_Block_Adminhtml_Subscription_View $block */
    protected $block;

    protected function setUp()
    {
        parent::setUp();
        $this->replaceRegistry('pss_subscription', $this->getModelMock('sheep_subscription/subscription'));
        $this->block = $this->getBlockMock('sheep_subscription/adminhtml_subscription_view', array('getUrl', 'toHtml', 'addButtons'));
    }

    public function testConstruct()
    {
        $this->assertEquals('sheep_subscription', EcomDev_Utils_Reflection::getRestrictedPropertyValue($this->block, '_blockGroup'));
        $this->assertEquals('adminhtml_subscription', EcomDev_Utils_Reflection::getRestrictedPropertyValue($this->block, '_controller'));
        $this->assertEquals('view', EcomDev_Utils_Reflection::getRestrictedPropertyValue($this->block, '_mode'));
        $this->assertEquals('sheep_subscription_view', $this->block->getId());

        $buttons = EcomDev_Utils_Reflection::getRestrictedPropertyValue($this->block, '_buttons');
        $this->assertArrayNotHasKey('save', $buttons);
        $this->assertArrayNotHasKey('reset', $buttons);
        $this->assertArrayNotHasKey('delete', $buttons);
    }

    public function testAddButtons()
    {
        $acl = $this->getModelMock('sheep_subscription/adminhtml_acl', array('canEditSubscription'));
        $acl->expects($this->once())->method('canEditSubscription')->willReturn(true);
        $this->replaceByMock('singleton', 'sheep_subscription/adminhtml_acl', $acl);

        $subscription = $this->getModelMock('sheep_subscription/subscription', array());

        $service = $this->getModelMock('sheep_subscription/service', array('canBeCancelled', 'canBePaused', 'canBeResumed'));
        $service->expects($this->once())->method('canBeCancelled')->with($subscription)->willReturn(true);
        $service->expects($this->once())->method('canBePaused')->with($subscription)->willReturn(false);
        $service->expects($this->once())->method('canBeResumed')->with($subscription)->willReturn(true);
        $this->replaceByMock('singleton', 'sheep_subscription/service', $service);

        $block = $this->getBlockMock('sheep_subscription/adminhtml_subscription_view', array('addButton', '_getModel', 'getCancelUrl', 'getResumeUrl'), false, array(), '', false);
        $block->expects($this->any())->method('_getModel')->willReturn($subscription);
        $block->expects($this->at(2))->method('addButton')->with('cancel');
        $block->expects($this->at(4))->method('addButton')->with('resume');

        $block->addButtons();
    }

    public function testAddButtonsWithoutPermissions()
    {
        $acl = $this->getModelMock('sheep_subscription/adminhtml_acl', array('canEditSubscription'));
        $acl->expects($this->once())->method('canEditSubscription')->willReturn(false);
        $this->replaceByMock('singleton', 'sheep_subscription/adminhtml_acl', $acl);

        $subscription = $this->getModelMock('sheep_subscription/subscription', array());
        $this->replaceRegistry('pss_subscription', $subscription);

        $service = $this->getModelMock('sheep_subscription/service', array('canBeCancelled', 'canBePaused', 'canBeResumed'));
        $service->expects($this->never())->method('canBeCancelled')->with($subscription)->willReturn(true);
        $service->expects($this->never())->method('canBePaused')->with($subscription)->willReturn(false);
        $service->expects($this->never())->method('canBeResumed')->with($subscription)->willReturn(false);
        $this->replaceByMock('singleton', 'sheep_subscription/service', $service);

        $block = $this->getBlockMock('sheep_subscription/adminhtml_subscription_view', array('addButton'), false, array(), '', false);
        $block->expects($this->never())->method('addButton');

        $block->addButtons();
    }

    public function testGetModel()
    {
        $this->replaceRegistry('pss_subscription', 'subscription registry');
        $actual = EcomDev_Utils_Reflection::invokeRestrictedMethod($this->block, '_getModel');
        $this->assertEquals('subscription registry', $actual);
    }

    public function testGetHeaderText()
    {
        $this->replaceRegistry('pss_subscription', new Varien_Object(array('id' => 200)));
        $actual = $this->block->getHeaderText();
        $this->assertEquals('View Subscription (ID: 200)', $actual);
    }

    public function testGetBackUrl()
    {
        $this->block->expects($this->once())->method('getUrl')->with('*/*/index');
        $this->block->getBackUrl();
    }

    public function testGetDeleteUrl()
    {
        $this->assertNull($this->block->getDeleteUrl());
    }

    public function testGetPauseUrl()
    {
        $this->replaceRegistry('pss_subscription', new Varien_Object(array('id' => 200)));
        $this->block->expects($this->once())->method('getUrl')->with('*/*/pause', array('subscription_id' => 200));
        $this->block->getPauseUrl();
    }

    public function testGetCancelUrl()
    {
        $this->replaceRegistry('pss_subscription', new Varien_Object(array('id' => 200)));
        $this->block->expects($this->once())->method('getUrl')->with('*/*/cancel', array('subscription_id' => 200));
        $this->block->getCancelUrl();
    }

    public function testGetResumeUrl()
    {
        $this->replaceRegistry('pss_subscription', new Varien_Object(array('id' => 200)));
        $this->block->expects($this->once())->method('getUrl')->with('*/*/resume', array('subscription_id' => 200));
        $this->block->getResumeUrl();
    }

}
