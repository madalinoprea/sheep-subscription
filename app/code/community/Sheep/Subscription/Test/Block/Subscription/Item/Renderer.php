<?php

/**
 * Class Sheep_Subscription_Test_Block_Subscription_Item_Renderer
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, {2015}, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_Block_Subscription_Item_Renderer
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Block_Subscription_Item_Renderer extends EcomDev_PHPUnit_Test_Case
{
    public function testInstanceOf()
    {
        $block = $this->getBlockMock('sheep_subscription/subscription_item_renderer', array('toHtml'));
        $this->assertInstanceOf('Mage_Checkout_Block_Cart_Item_Renderer', $block);
        $this->assertInstanceOf('Sheep_Subscription_Block_Subscription_Item_Renderer_Interface', $block);
    }


    public function testCanEdit()
    {
        $block = $this->getBlockMock('sheep_subscription/subscription_item_renderer', array('toHtml'));
        $canEdit = $block->canEdit();
        $this->assertFalse($canEdit);
    }

}

