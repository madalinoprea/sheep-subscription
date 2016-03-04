<?php

/**
 * Class Sheep_Subscription_Test_Block_Subscription_Item_Renderer_Bundle
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, {2015}, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_Block_Subscription_Item_Renderer_Bundle
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Block_Subscription_Item_Renderer_Bundle extends EcomDev_PHPUnit_Test_Case
{
    public function testInstanceOf()
    {
        $block = $this->getBlockMock('sheep_subscription/subscription_item_renderer_bundle', array('toHtml'));
        $this->assertInstanceOf('Mage_Bundle_Block_Checkout_Cart_Item_Renderer', $block);
        $this->assertInstanceOf('Sheep_Subscription_Block_Subscription_Item_Renderer_Interface', $block);
    }
    

    public function testCanEdit()
    {
        $block = $this->getBlockMock('sheep_subscription/subscription_item_renderer_bundle', array('toHtml'));
        $this->assertFalse($block->canEdit());
    }

}

