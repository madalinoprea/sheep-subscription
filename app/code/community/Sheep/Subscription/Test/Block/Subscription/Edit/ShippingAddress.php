<?php

/**
 * Class Sheep_Subscription_Test_Block_Subscription_Edit_ShippingAddress
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers   Sheep_Subscription_Block_Subscription_Edit_ShippingAddress
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Block_Subscription_Edit_ShippingAddress extends EcomDev_PHPUnit_Test_Case
{
    protected $block;

    protected function setUp()
    {
        parent::setUp();
        $this->block = $this->getBlockMock('sheep_subscription/subscription_edit_shippingAddress', array('toHtml'));
    }

    public function testGetSubscription()
    {
        $this->replaceRegistry('pss_subscription', 'subscription registry');
        $actual = $this->block->getSubscription();
        $this->assertEquals('subscription registry', $actual);
    }

    public function testGetBackUrl()
    {
        $helper = $this->getHelperMock('sheep_subscription', array('getSubscriptionUrl'));
        $helper->expects($this->once())->method('getSubscriptionUrl')->with(100);
        $this->replaceByMock('helper', 'sheep_subscription', $helper);

        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getId'));
        $subscription->expects($this->any())->method('getId')->willReturn(100);

        $block = $this->getBlockMock('sheep_subscription/subscription_edit_shippingAddress', array('getSubscription'));
        $block->expects($this->any())->method('getSubscription')->willReturn($subscription);

        $block->getBackUrl();
    }

    public function testGetSaveShippingAddressUrl()
    {
        $helper = $this->getHelperMock('sheep_subscription', array('getSaveShippingAddressUrl'));
        $helper->expects($this->once())->method('getSaveShippingAddressUrl')->with(100);
        $this->replaceByMock('helper', 'sheep_subscription', $helper);

        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getId'));
        $subscription->expects($this->any())->method('getId')->willReturn(100);

        $block = $this->getBlockMock('sheep_subscription/subscription_edit_shippingAddress', array('getSubscription'));
        $block->expects($this->any())->method('getSubscription')->willReturn($subscription);

        $block->getSaveShippingAddressUrl();
    }

    public function testGetQuote()
    {
        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getQuote'));
        $subscription->expects($this->once())->method('getQuote');

        $block = $this->getBlockMock('sheep_subscription/subscription_edit_shippingAddress', array('getSubscription'));
        $block->expects($this->any())->method('getSubscription')->willReturn($subscription);

        $block->getQuote();
    }

    public function testGetAddress()
    {
        $quote = $this->getModelMock('sales/quote', array('getShippingAddress'));
        $quote->expects($this->once())->method('getShippingAddress');

        $block = $this->getBlockMock('sheep_subscription/subscription_edit_shippingAddress', array('getQuote', 'getSubscription'));
        $block->expects($this->any())->method('getQuote')->willReturn($quote);

        $block->getAddress();
    }

    public function testGetCheckout()
    {
        $this->assertNull($this->block->getCheckout());
    }

    public function testGetAddressesHtmlSelect()
    {
        $address = $this->getModelMock('customer/address', array('getId', 'format'));
        $address->expects($this->any())->method('getId')->willReturn(501);
        $address->expects($this->any())->method('format')->with('oneline')->willReturn('address description');

        $quoteAddress = $this->getModelMock('sales/quote_address', array('getCustomerAddressId'));
        $quoteAddress->expects($this->any())->method('getCustomerAddressId')->willReturn('');

        $customer = $this->getModelMock('customer/customer', array('getAddresses', 'getPrimaryShippingAddress'));
        $customer->expects($this->any())->method('getAddresses')->willReturn(
            array($address)
        );
        $customer->expects($this->any())->method('getPrimaryShippingAddress')->willReturn($address);


        $selectBlock = $this->getBlockMock('core/html_select', array('setName', 'setId', 'setClass', 'setExtraParams', 'setValue', 'setOptions', '_toHtml', 'addOption'));
        $selectBlock->expects($this->once())->method('setName')->with('shipping_address_id')->willReturnSelf();
        $selectBlock->expects($this->once())->method('setId')->with('shipping-address-select')->willReturnSelf();
        $selectBlock->expects($this->once())->method('setClass')->with('address-select')->willReturnSelf();
        $selectBlock->expects($this->never())->method('setExtraParams');
        $selectBlock->expects($this->once())->method('setValue')->with(501)->willReturnSelf();
        $selectBlock->expects($this->once())->method('setOptions')->with(
            array(
                array('value' => 501, 'label' => 'address description')
            )
        )->willReturnSelf();
        $selectBlock->expects($this->once())->method('addOption')->with('', 'New Address');
        $selectBlock->expects($this->once())->method('_toHtml')->willReturn('html select');


        $layout = $this->getModelMock('core/layout', array('createBlock'));
        $layout->expects($this->once())->method('createBlock')->with('core/html_select')->willReturn($selectBlock);

        $block = $this->getBlockMock('sheep_subscription/subscription_edit_shippingAddress',
            array('isCustomerLoggedIn', 'getCustomer', 'getAddress', 'getLayout'));
        $block->expects($this->any())->method('isCustomerLoggedIn')->willReturn(true);
        $block->expects($this->any())->method('getCustomer')->willReturn($customer);
        $block->expects($this->any())->method('getAddress')->willReturn($quoteAddress);
        $block->expects($this->any())->method('getLayout')->willReturn($layout);

        $actual = $block->getAddressesHtmlSelect('shipping');
        $this->assertEquals('html select', $actual);
    }
}

