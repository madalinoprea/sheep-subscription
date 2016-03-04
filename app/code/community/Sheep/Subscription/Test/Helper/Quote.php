<?php

/**
 * Class Sheep_Subscription_Test_Helper_Quote
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, {2015}, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_Helper_Quote
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Helper_Quote extends EcomDev_PHPUnit_Test_Case
{
    /** @var Sheep_Subscription_Helper_Quote $helper */
    protected $helper;

    protected function setUp()
    {
        parent::setUp();
        $this->helper = Mage::helper('sheep_subscription/quote');
    }

    public function testIsSubscriptionQuoteItemWithoutOptions()
    {
        $buyRequest = new Varien_Object();
        $quoteItem = $this->getModelMock('sales/quote_item', array('getBuyRequest'));
        $quoteItem->expects($this->once())->method('getBuyRequest')->willReturn($buyRequest);

        $actual = $this->helper->isSubscriptionQuoteItem($quoteItem);
        $this->assertFalse($actual);
    }

    public function testIsSubscriptionQuoteItemWithOptions()
    {
        $buyRequest = new Varien_Object(array('options' => array('option_id_100' => 'something')));
        $quoteItem = $this->getModelMock('sales/quote_item', array('getBuyRequest'));
        $quoteItem->expects($this->once())->method('getBuyRequest')->willReturn($buyRequest);

        $actual = $this->helper->isSubscriptionQuoteItem($quoteItem);
        $this->assertFalse($actual);
    }

    public function testIsSubscriptionQuoteItemWithSubscriptionOptions()
    {
        $buyRequest = new Varien_Object(array('options' => array('option_id_100' => 'something', Sheep_Subscription_Model_Service::SUBSCRIPTION_PRODUCT_OPTION_ID => 'type_id_100')));
        $quoteItem = $this->getModelMock('sales/quote_item', array('getBuyRequest'));
        $quoteItem->expects($this->once())->method('getBuyRequest')->willReturn($buyRequest);

        $actual = $this->helper->isSubscriptionQuoteItem($quoteItem);
        $this->assertTrue($actual);
    }

    public function testGetSubscriptionTypeId()
    {
        $quoteItem = $this->getModelMock('sales/quote_item', array('getBuyRequest'));
        $quoteItem->expects($this->once())->method('getBuyRequest')->willReturn(new Varien_Object(
            array('options' => array(
                Sheep_Subscription_Model_Service::SUBSCRIPTION_PRODUCT_OPTION_ID => Sheep_Subscription_Model_Service::SUBSCRIPTION_PRODUCT_OPTION_TYPE_VALUE_ID_PREFIX . 20
            ))
        ));

        $actual = $this->helper->getSubscriptionTypeId($quoteItem);
        $this->assertEquals(20, $actual);
    }

    public function testGetSubscriptionTypeIdWithoutSubscription()
    {
        $quoteItem = $this->getModelMock('sales/quote_item', array('getBuyRequest'));
        $quoteItem->expects($this->once())->method('getBuyRequest')->willReturn(new Varien_Object(
            array('options' => array(
                'some_option' => 'with_value'
            ))
        ));

        $actual = $this->helper->getSubscriptionTypeId($quoteItem);
        $this->assertNull($actual);
    }

    public function testGetSubscriptionType()
    {
        $quoteItem = $this->getModelMock('sales/quote_item');

        $type = $this->getModelMock('sheep_subscription/type', array('load', 'getId'));
        $type->expects($this->any())->method('getId')->willReturn(5);
        $type->expects($this->once())->method('load')->with(5)->willReturnSelf();
        $this->replaceByMock('model', 'sheep_subscription/type', $type);

        $helper = $this->getHelperMock('sheep_subscription/quote', array('getSubscriptionTypeId'));
        $helper->expects($this->once())->method('getSubscriptionTypeId')->with($quoteItem)->willReturn(5);

        $actual = $helper->getSubscriptionType($quoteItem);
        $this->assertNotNull($actual);
        $this->assertInstanceOf('Sheep_Subscription_Model_Type', $actual);
        $this->assertEquals($actual, $type);
    }

    public function testGetSubscriptionTypeWithoutType()
    {
        $quoteItem = $this->getModelMock('sales/quote_item');

        $type = $this->getModelMock('sheep_subscription/type', array('load'));
        $type->expects($this->never())->method('load');
        $this->replaceByMock('model', 'sheep_subscription/type', $type);

        $helper = $this->getHelperMock('sheep_subscription/quote', array('getSubscriptionTypeId'));
        $helper->expects($this->once())->method('getSubscriptionTypeId')->with($quoteItem)->willReturn(null);

        $actual = $helper->getSubscriptionType($quoteItem);
        $this->assertNull($actual);
    }

    public function testGetSubscriptionTypeWithInvalidType()
    {
        $quoteItem = $this->getModelMock('sales/quote_item');

        $type = $this->getModelMock('sheep_subscription/type', array('load', 'getId'));
        $type->expects($this->any())->method('getId')->willReturn(null);
        $type->expects($this->once())->method('load')->with(5)->willReturnSelf();
        $this->replaceByMock('model', 'sheep_subscription/type', $type);

        $helper = $this->getHelperMock('sheep_subscription/quote', array('getSubscriptionTypeId'));
        $helper->expects($this->once())->method('getSubscriptionTypeId')->with($quoteItem)->willReturn(5);

        $actual = $helper->getSubscriptionType($quoteItem);
        $this->assertNull($actual);
    }
}
