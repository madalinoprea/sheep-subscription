<?php

/**
 * Class Sheep_Subscription_Test_Block_Adminhtml_Subscription_Items
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, {2015}, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_Block_Adminhtml_Subscription_Items
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Block_Adminhtml_Subscription_Items extends EcomDev_PHPUnit_Test_Case
{
    /** @var Sheep_Subscription_Block_Adminhtml_Subscription_Items $block */
    protected $block;

    protected function setUp()
    {
        parent::setUp();
        $this->block = $this->getBlockMock('sheep_subscription/adminhtml_subscription_items', array('toHtml', 'getSubscription'));
    }

    public function testConstruct()
    {
        $this->assertEquals('sheep_subscription/subscription/items.phtml', $this->block->getTemplate());
        $itemRenders = EcomDev_Utils_Reflection::getRestrictedPropertyValue($this->block, '_itemRenders');
        $this->assertNotNull($itemRenders);
        $this->assertArrayHasKey('default', $itemRenders);
        $this->assertEquals('sheep_subscription/adminhtml_subscription_items_renderer_default', $itemRenders['default']['block']);
        $this->assertEquals('sheep_subscription/subscription/items/renderer/default.phtml', $itemRenders['default']['template']);
    }

    public function testGetSubscription()
    {
        $this->replaceRegistry('pss_subscription', 'subscription registry');
        $block = $this->getBlockMock('sheep_subscription/adminhtml_subscription_items', array('toHtml'));
        $actual = $block->getSubscription();
        $this->assertEquals('subscription registry', $actual);
    }

    public function testGetItemsCollection()
    {
        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getItemsCollection'));
        $subscription->expects($this->once())->method('getItemsCollection');

        $this->block->expects($this->once())->method('getSubscription')->willReturn($subscription);
        $this->block->getItemsCollection();
    }

    public function testCanEditQty()
    {
        $this->assertFalse($this->block->canEditQty());
    }

}
