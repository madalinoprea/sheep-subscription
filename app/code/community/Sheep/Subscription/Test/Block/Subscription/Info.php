<?php

/**
 * Class Sheep_Subscription_Test_Block_Subscription_Info
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers   Sheep_Subscription_Block_Subscription_Info
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Block_Subscription_Info extends EcomDev_PHPUnit_Test_Case
{
    protected $block;

    protected function setUp()
    {
        parent::setUp();
        $this->block = $this->getBlockMock('sheep_subscription/subscription_info', array('toHtml'));
    }

    public function testGetSubscription()
    {
        $this->replaceRegistry('pss_subscription', 'subscription registry');
        $actual = $this->block->getSubscription();
        $this->assertEquals('subscription registry', $actual);
    }

    public function testLinks()
    {
        $this->block->addLink('link #1', 'url1');
        $this->block->addLink('link #2', 'url2');
        $this->block->addLink('link #3', '');

        $actual = $this->block->getLinks();
        $this->assertNotNull($actual);
        $this->assertCount(3, $actual);

        $this->assertEquals('link #1', $actual['link #1']->getLabel());
        $this->assertEquals('url1', $actual['link #1']->getUrl());

        $this->assertEquals('link #2', $actual['link #2']->getLabel());
        $this->assertEquals('url2', $actual['link #2']->getUrl());

        $this->assertEquals('link #3', $actual['link #3']->getLabel());
        $this->assertEquals('#', $actual['link #3']->getUrl());
    }

    public function testGetService()
    {
        $actual = $this->block->getService();
        $this->assertNotNull($actual);
        $this->assertInstanceOf('Sheep_Subscription_Model_Service', $actual);
    }

    /**
     * Test that this functionality is delegated to our helper
     */
    public function testAllowCustomerManagement()
    {
        $helper = $this->getHelperMock('sheep_subscription', array('getIsAccountManagementAllowed'));
        $helper->expects($this->once())->method('getIsAccountManagementAllowed')->willReturn(true);
        $this->replaceByMock('helper', 'sheep_subscription', $helper);

        $result = $this->block->allowCustomerManagement();
        $this->assertTrue($result);
    }


    /**
     * Tests that subscription management links are not added when setting is disabled.
     */
    public function testAddSubscriptionManagementLinksWithSettingDisabled()
    {
        $subscriptionMock = $this->getModelMock('sheep_subscription/subscription', array('getId', 'getOriginalOrderId', 'getPayment'));
        $subscriptionMock->expects($this->any())->method('getId')->willReturn(1000);

        $serviceMock = $this->getModelMock('sheep_subscription/service', array('canBePaused', 'canBeResumed', 'canBeCancelled'));
        $serviceMock->expects($this->never())->method('canBePaused')->willReturn(true);
        $serviceMock->expects($this->never())->method('canBeResumed')->willReturn(false);
        $serviceMock->expects($this->never())->method('canBeCancelled')->willReturn(true);

        /** @var Sheep_Subscription_Block_Subscription_Info $block */
        $block = $this->getBlockMock('sheep_subscription/subscription_info', array('getSubscription', 'getService', 'allowCustomerManagement'));
        $block->expects($this->any())->method('getSubscription')->willReturn($subscriptionMock);
        $block->expects($this->any())->method('getService')->willReturn($serviceMock);
        $block->expects($this->once())->method('allowCustomerManagement')->willReturn(false);

        $block->addSubscriptionManagementLinks();
        $links = $block->getLinks();

        $this->assertNotNull($links);
        $this->assertEmpty($links);
    }


    /**
     * Verifies that subscription management links are added when setting is enabled
     */
    public function testAddSubscriptionManagementLinksWithSettingEnabled()
    {
        $helperMock = $this->getHelperMock('sheep_subscription');
        $this->replaceByMock('helper', 'sheep_subscription', $helperMock);

        $subscriptionMock = $this->getModelMock('sheep_subscription/subscription', array('getId', 'getOriginalOrderId', 'getPayment'));
        $subscriptionMock->expects($this->any())->method('getId')->willReturn(1000);

        $serviceMock = $this->getModelMock('sheep_subscription/service', array('canBePaused', 'canBeResumed', 'canBeCancelled'));
        $serviceMock->expects($this->once())->method('canBePaused')->willReturn(true);
        $serviceMock->expects($this->once())->method('canBeResumed')->willReturn(false);
        $serviceMock->expects($this->once())->method('canBeCancelled')->willReturn(true);

        $block = $this->getBlockMock('sheep_subscription/subscription_info', array('getSubscription', 'getService', 'allowCustomerManagement'));
        $block->expects($this->any())->method('getSubscription')->willReturn($subscriptionMock);
        $block->expects($this->any())->method('getService')->willReturn($serviceMock);
        $block->expects($this->once())->method('allowCustomerManagement')->willReturn(true);

        $block->addSubscriptionManagementLinks();
        $links = $block->getLinks();

        $this->assertNotNull($links);
        $this->assertNotEmpty($links);

        $this->assertArrayHasKey('Pause', $links);
        $this->assertArrayHasKey('Cancel', $links);
    }

    /**
     * Tests that
     *  - subscription id is used to set title on head block
     *  - subscription original order id is added as a link
     */
    public function testPrepareLayout()
    {
        $subscriptionMock = $this->getModelMock('sheep_subscription/subscription', array('getId', 'getOriginalOrderId'));
        $subscriptionMock->expects($this->any())->method('getId')->willReturn(1000);
        $subscriptionMock->expects($this->any())->method('getOriginalOrderId')->willReturn(10);

        $headBlock = $this->getBlockMock('core/template', array('setTitle'));
        $headBlock->expects($this->once())->method('setTitle')->with('Subscription # 1000');

        $layoutMock = $this->getModelMock('core/layout', array('getBlock'));
        $layoutMock->expects($this->at(0))->method('getBlock')->with('head')->willReturn($headBlock);

        $block = $this->getBlockMock('sheep_subscription/subscription_info', array('getLayout', 'getSubscription', 'addSubscriptionManagementLinks', 'getUrl'));
        $block->expects($this->any())->method('getLayout')->willReturn($layoutMock);
        $block->expects($this->once())->method('getSubscription')->willReturn($subscriptionMock);
        $block->expects($this->once())->method('addSubscriptionManagementLinks');

        EcomDev_Utils_Reflection::invokeRestrictedMethod($block, '_prepareLayout');

        // Test that relevant links were added
        $links = $block->getLinks();
        $this->assertNotNull($links);
        $this->assertCount(1, $links);
    }


    public function testCanChangeRenewalDate()
    {
        $subscription = $this->getModelMock('sheep_subscription/subscription', array('load'));

        $service = $this->getModelMock('sheep_subscription/service', array('canChangeRenewalDate'));
        $service->expects($this->once())->method('canChangeRenewalDate')->with($subscription)->willReturn(true);


        $block = $this->getBlockMock('sheep_subscription/subscription_info', array('getService', 'getSubscription'));
        $block->expects($this->any())->method('getService')->willReturn($service);
        $block->expects($this->once())->method('getSubscription')->willReturn($subscription);

        $actual = $block->canChangeRenewalDate();
        $this->assertTrue($actual);
    }


    public function testGetFormattedRenewalDate()
    {
        $renewal = $this->getModelMock('sheep_subscription/renewal', array('getDateStoreDate'));
        $renewal->expects($this->once())->method('getDateStoreDate')->willReturn(new Zend_Date(strtotime('2015-12-03 02:00:00')));

        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getNextRenewal'));
        $subscription->expects($this->any())->method('getNextRenewal')->willReturn($renewal);

        $block = $this->getBlockMock('sheep_subscription/subscription_info', array('getSubscription'));
        $block->expects($this->any())->method('getSubscription')->willReturn($subscription);

        $actual = $block->getFormattedRenewalDate();
        $this->assertEquals('2015-12-03', $actual); // See TZ difference

    }

}

