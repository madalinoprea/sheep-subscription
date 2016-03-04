<?php

/**
 * Class Sheep_Subscription_Test_Block_Subscription_Items
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, {2015}, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_Block_Subscription_Items
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Block_Subscription_Items extends EcomDev_PHPUnit_Test_Case
{
    protected $block;

    protected function setUp()
    {
        parent::setUp();

        $layoutMock = $this->getModelMock('core/layout', array('createBlock'));
        $layoutMock->expects($this->any())->method('createBlock')
            ->willReturn($this->getBlockMock('core/template', array('toHtml')));


        $this->block = $this->getBlockMock('sheep_subscription/subscription_items', array('getLayout'));
        $this->block->expects($this->any())->method('getLayout')->willReturn($layoutMock);
    }


    public function testConstruct()
    {
        $itemRenders = EcomDev_Utils_Reflection::getRestrictedPropertyValue($this->block, '_itemRenders');
        $this->assertNotNull($itemRenders);
        $this->assertArrayHasKey('default', $itemRenders);
        $this->assertEquals('sheep_subscription/subscription_item_renderer', $itemRenders['default']['block']);
        $this->assertEquals('sheep_subscription/subscription/item/default.phtml', $itemRenders['default']['template']);

        $actual = $this->block->getItemRenderer('default');
        $this->assertNotNull($actual);
    }

    public function testGetSubscription()
    {
        $this->replaceRegistry('pss_subscription', 'subscription registry');
        $actual = $this->block->getSubscription();
        $this->assertEquals('subscription registry', $actual);
    }

    public function testGetSubscriptionWithAssignedData()
    {
        $this->replaceRegistry('pss_subscription', null);
        $this->block->setData('subscription', 'subscription set on block');
        $actual = $this->block->getSubscription();
        $this->assertEquals('subscription set on block', $actual);
    }

}
