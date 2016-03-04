<?php

/**
 * Class Sheep_Subscription_Test_Block_Adminhtml_Subscription_View_Tabs
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, {2015}, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers   Sheep_Subscription_Block_Adminhtml_Subscription_View_Tabs
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Block_Adminhtml_Subscription_View_Tabs extends EcomDev_PHPUnit_Test_Case
{
    /** @var Sheep_Subscription_Block_Adminhtml_Subscription_View_Tabs $block */
    protected $block;

    protected function setUp()
    {
        parent::setUp();
        $this->replaceByMock('singleton', 'admin/session', $this->getModelMock('admin/session', array('init')));

        $layoutMock = $this->getModelMock('core/layout', array('createBlock'));
        $layoutMock->expects($this->any())->method('createBlock')->willReturn($this->getBlockMock('core/text', array('toHtml')));

        $this->block = $this->getBlockMock('sheep_subscription/adminhtml_subscription_view_tabs', array('toHtml', 'getLayout', 'getUrl'));
        $this->block->expects($this->any())->method('getLayout')->willReturn($layoutMock);
    }


    public function testConstruct()
    {
        $this->assertEquals('sheep_subscription_view_tabs', $this->block->getId());
        $this->assertEquals('sheep_subscription_view', $this->block->getDestElementId());
        $this->assertEquals('Subscription View', $this->block->getTitle());
    }


    public function testBeforeHtml()
    {
        $acl = $this->getModelMock('sheep_subscription/adminhtml_acl', array('canViewSubscriptionDetails', 'canViewSubscriptionRenewals'));
        $acl->expects($this->once())->method('canViewSubscriptionDetails')->willReturn(true);
        $acl->expects($this->once())->method('canViewSubscriptionRenewals')->willReturn(true);
        $this->replaceByMock('singleton', 'sheep_subscription/adminhtml_acl', $acl);


        EcomDev_Utils_Reflection::invokeRestrictedMethod($this->block, '_beforeToHtml');
        $tabs = $this->block->getTabsIds();
        $this->assertContains('subscription_info', $tabs);
        $this->assertContains('subscription_renewals', $tabs);
    }


    public function testBeforeHtmlWithoutPrivileges()
    {
        $acl = $this->getModelMock('sheep_subscription/adminhtml_acl', array('canViewSubscriptionDetails', 'canViewSubscriptionRenewals'));
        $acl->expects($this->once())->method('canViewSubscriptionDetails')->willReturn(true);
        $acl->expects($this->once())->method('canViewSubscriptionRenewals')->willReturn(false);
        $this->replaceByMock('singleton', 'sheep_subscription/adminhtml_acl', $acl);


        EcomDev_Utils_Reflection::invokeRestrictedMethod($this->block, '_beforeToHtml');
        $tabs = $this->block->getTabsIds();
        $this->assertContains('subscription_info', $tabs);
        $this->assertNotContains('subscription_renewals', $tabs);
    }

}
