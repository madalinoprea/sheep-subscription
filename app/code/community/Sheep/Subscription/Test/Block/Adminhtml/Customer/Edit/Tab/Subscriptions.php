<?php

/**
 * Class Sheep_Subscription_Test_Block_Adminhtml_Customer_Edit_Tab_Subscriptions
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, {2015}, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_Block_Adminhtml_Customer_Edit_Tab_Subscriptions
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Block_Adminhtml_Customer_Edit_Tab_Subscriptions extends EcomDev_PHPUnit_Test_Case
{
    /** @var Sheep_Subscription_Block_Adminhtml_Customer_Edit_Tab_Subscriptions $block */
    protected $block;

    protected function setUp()
    {
        parent::setUp();

        $this->replaceByMock('singleton', 'adminhtml/session', $this->getModelMock('admin/session', array('init')));

        $this->block = $this->getBlockMock('sheep_subscription/adminhtml_customer_edit_tab_subscriptions', array('getUrl', 'getLayout', '_preparePage', '_afterLoadCollection'));

        $layoutMock = $this->getModelMock('core/layout', array('createBlock'));
        $layoutMock->expects($this->any())->method('createBlock')->will($this->returnValue($this->getBlockMock('core/text', array('toHtml'))));
        $this->block->expects($this->any())->method('getLayout')->will($this->returnValue($layoutMock));
    }

    public function testGetCustomer()
    {
        $object = $this->getBlockMock('sheep_subscription/adminhtml_customer_edit_tab_subscriptions', array('toHtml'));

        $object->setCustomerId(222);
        $this->assertInstanceOf('Sheep_Subscription_Block_Adminhtml_Subscription_Grid', $object);
        $this->assertEquals(222, $object->getCustomerId());
    }

    public function testPrepareCollection()
    {
        // Assert that customer filter is set and quote data is added
        $subscriptionCollection = $this->getResourceModelMock('sheep_subscription/subscription_collection', array('addCustomerFilter', 'addQuoteData', 'load', 'save'));
        $subscriptionCollection->expects($this->once())->method('addCustomerFilter')->with(100)->willReturnSelf();
        $subscriptionCollection->expects($this->once())->method('addQuoteData')->with(array('quote_currency_code', 'customer_email', 'subtotal'))->willReturnSelf();
        $this->replaceByMock('resource_model', 'sheep_subscription/subscription_collection', $subscriptionCollection);

        $this->block->setCustomerId(100);
        EcomDev_Utils_Reflection::invokeRestrictedMethod($this->block, '_prepareCollection');
        $actual = $this->block->getCollection();
        $this->assertNotNull($actual);
        $this->assertInstanceOf('Sheep_Subscription_Model_Resource_Subscription_Collection', $actual);
    }

    public function testPrepareColumns()
    {
        EcomDev_Utils_Reflection::invokeRestrictedMethod($this->block, '_prepareColumns');
        $actual = $this->block->getColumns();

        $this->assertNotEmpty($actual);
        $this->assertArrayHasKey('id', $actual);
        $this->assertArrayHasKey('status', $actual);
        $this->assertArrayHasKey('type', $actual);
        $this->assertArrayHasKey('subtotal', $actual);
        $this->assertArrayHasKey('start_date', $actual);
        $this->assertArrayHasKey('created_at', $actual);
        $this->assertArrayNotHasKey('customer', $actual);
    }

    public function testGetGridUrl()
    {
        $this->block->expects($this->once())->method('getUrl')->with('*/*/customerTab');
        $this->block->getGridUrl();
    }

}

