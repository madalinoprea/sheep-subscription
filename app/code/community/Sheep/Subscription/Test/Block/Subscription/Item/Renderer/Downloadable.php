<?php

/**
 * Class Sheep_Subscription_Test_Block_Subscription_Item_Renderer_Downloadable
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, {2015}, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_Block_Subscription_Item_Renderer_Downloadable
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Block_Subscription_Item_Renderer_Downloadable extends EcomDev_PHPUnit_Test_Case
{
    public function testInstanceOf()
    {
        $block = $this->getBlockMock('sheep_subscription/subscription_item_renderer_downloadable', array('toHtml'));
        $this->assertInstanceOf('Mage_Downloadable_Block_Checkout_Cart_Item_Renderer', $block);
        $this->assertInstanceOf('Sheep_Subscription_Block_Subscription_Item_Renderer_Interface', $block);
    }
    

    public function testCanEdit()
    {
        $block = $this->getBlockMock('sheep_subscription/subscription_item_renderer_downloadable', array('toHtml'));
        $this->assertFalse($block->canEdit());
    }

}

