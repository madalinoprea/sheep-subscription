<?php

/**
 * Class Sheep_Subscription_Test_Block_Subscription_Info
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers   Sheep_Subscription_Block_Cart_Item_Info
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Block_Cart_Item_Info extends EcomDev_PHPUnit_Test_Case
{
    protected $block;

    protected function setUp()
    {
        parent::setUp();
        $this->block = $this->getBlockMock('sheep_subscription/cart_item_info', array('toHtml'));
    }

    public function testInit()
    {
        $product = $this->getModelMock('catalog/product');

        $checkoutHelper = $this->getHelperMock('checkout', array('formatPrice'));
        $checkoutHelper->expects($this->any())->method('formatPrice')->with(-50)->willReturn('-$50.00');
        $this->replaceByMock('helper', 'checkout', $checkoutHelper);

        $item = $this->getModelMock('sales/quote_item', array('getProduct', 'getQty'));
        $item->expects($this->any())->method('getProduct')->willReturn($product);
        $item->expects($this->any())->method('getQty')->willReturn(2);

        $type = $this->getModelMock('sheep_subscription/type', array('getId', 'getPrice', 'getPriceType'));
        $type->expects($this->any())->method('getId')->willReturn(5);
        $type->expects($this->any())->method('getPrice')->willReturn(25);
        $type->expects($this->any())->method('getPriceType')->willReturn('');

        $types = $this->getResourceModelMock('sheep_subscription/type_collection', array('load', 'addFieldToFilter', 'getItemById'));
        $types->expects($this->once())->method('addFieldToFilter')->with('id', 5)->willReturnSelf();
        $types->expects($this->once())->method('getItemById')->with(5)->willReturn($type);
        $this->replaceByMock('resource_model', 'sheep_subscription/type_collection', $types);

        $service = $this->getModelMock('sheep_subscription/service', array('addProductPriceToType'));
        $service->expects($this->once())->method('addProductPriceToType')->with($product, $types);
        $this->replaceByMock('model', 'sheep_subscription/service', $service);

        $helper = $this->getHelperMock('sheep_subscription/quote', array('isSubscriptionQuoteItem', 'getSubscriptionType'));
        $helper->expects($this->once())->method('isSubscriptionQuoteItem')->with($item)->willReturn(true);
        $helper->expects($this->once())->method('getSubscriptionType')->with($item)->willReturn($type);
        $this->replaceByMock('helper', 'sheep_subscription/quote', $helper);

        $additionalBlock = $this->getBlockMock('core/text_list', array('getItem'));
        $additionalBlock->expects($this->any())->method('getItem')->willReturn($item);

        $layout = $this->getModelMock('core/layout', array('getBlock'));
        $layout->expects($this->once())->method('getBlock')->with('additional.product.info')->willReturn($additionalBlock);

        $block= $this->getBlockMock('sheep_subscription/cart_item_info', array('getLayout'));
        $block->expects($this->any())->method('getLayout')->willReturn($layout);

        $block->init();

        $this->assertEquals($item, $block->getItem());
        $this->assertContains('-$50.00', $block->getSubscriptionDiscount());
    }


    public function testInitWithPercentDiscount()
    {
        $product = $this->getModelMock('catalog/product');

        $item = $this->getModelMock('sales/quote_item', array('getProduct', 'getQty'));
        $item->expects($this->any())->method('getProduct')->willReturn($product);
        $item->expects($this->any())->method('getQty')->willReturn(2);

        $type = $this->getModelMock('sheep_subscription/type', array('getId', 'getPrice', 'getPriceType'));
        $type->expects($this->any())->method('getId')->willReturn(5);
        $type->expects($this->any())->method('getPrice')->willReturn(25);
        $type->expects($this->any())->method('getPriceType')->willReturn('percent');

        $types = $this->getResourceModelMock('sheep_subscription/type_collection', array('load', 'addFieldToFilter', 'getItemById'));
        $types->expects($this->once())->method('addFieldToFilter')->with('id', 5)->willReturnSelf();
        $types->expects($this->once())->method('getItemById')->with(5)->willReturn($type);
        $this->replaceByMock('resource_model', 'sheep_subscription/type_collection', $types);

        $service = $this->getModelMock('sheep_subscription/service', array('addProductPriceToType'));
        $service->expects($this->once())->method('addProductPriceToType')->with($product, $types);
        $this->replaceByMock('model', 'sheep_subscription/service', $service);

        $helper = $this->getHelperMock('sheep_subscription/quote', array('isSubscriptionQuoteItem', 'getSubscriptionType'));
        $helper->expects($this->once())->method('isSubscriptionQuoteItem')->with($item)->willReturn(true);
        $helper->expects($this->once())->method('getSubscriptionType')->with($item)->willReturn($type);
        $this->replaceByMock('helper', 'sheep_subscription/quote', $helper);

        $additionalBlock = $this->getBlockMock('core/text_list', array('getItem'));
        $additionalBlock->expects($this->any())->method('getItem')->willReturn($item);

        $layout = $this->getModelMock('core/layout', array('getBlock'));
        $layout->expects($this->once())->method('getBlock')->with('additional.product.info')->willReturn($additionalBlock);

        $block= $this->getBlockMock('sheep_subscription/cart_item_info', array('getLayout'));
        $block->expects($this->any())->method('getLayout')->willReturn($layout);

        $block->init();

        $this->assertEquals($item, $block->getItem());
        $this->assertContains('25%', $block->getSubscriptionDiscount());
    }

}
