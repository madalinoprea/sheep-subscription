<?php

/**
 * Class Sheep_Subscription_Test_Block_Adminhtml_Subscription_Grid
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, {2015}, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_Block_Adminhtml_Subscription_Grid
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Block_Adminhtml_Subscription_Grid extends EcomDev_PHPUnit_Test_Case
{
    /** @var Sheep_Subscription_Block_Adminhtml_Subscription_Grid $block */
    protected $block;

    protected function setUp()
    {
        parent::setUp();

        $this->replaceByMock('singleton', 'adminhtml/session', $this->getModelMock('admin/session', array('init')));

        $layoutMock = $this->getModelMock('core/layout', array('createBlock'));
        $layoutMock->expects($this->any())->method('createBlock')->willReturn($this->getBlockMock('core/text', array('toHtml')));

        $this->block = $this->getBlockMock('sheep_subscription/adminhtml_subscription_grid', array('getLayout', 'toHtml', 'getUrl'));
        $this->block->expects($this->any())->method('getLayout')->willReturn($layoutMock);
    }

    public function testConstruct()
    {
        $this->assertEquals('subscription_grid', $this->block->getId());
        $this->assertEquals('id',  EcomDev_Utils_Reflection::getRestrictedPropertyValue($this->block, '_defaultSort'));
        $this->assertEquals('DESC',  EcomDev_Utils_Reflection::getRestrictedPropertyValue($this->block, '_defaultDir'));
        $this->assertFalse(EcomDev_Utils_Reflection::getRestrictedPropertyValue($this->block, '_saveParametersInSession'));
        $this->assertTrue($this->block->getUseAjax());
    }

    public function testPrepareCollection()
    {
        // Assert that customer filter is set and quote data is added
        $subscription = $this->getResourceModelMock('sheep_subscription/subscription_collection', array('addCustomerFilter', 'addQuoteData', 'load', 'save'));
        $subscription->expects($this->once())->method('addQuoteData')->with(array('quote_currency_code', 'customer_email', 'subtotal'))->willReturnSelf();
        $this->replaceByMock('resource_model', 'sheep_subscription/subscription_collection', $subscription);

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
        $this->assertArrayHasKey('customer', $actual);
        $this->assertArrayHasKey('status', $actual);
        $this->assertArrayHasKey('type', $actual);
        $this->assertArrayHasKey('subtotal', $actual);
        $this->assertArrayHasKey('start_date', $actual);
        $this->assertArrayHasKey('created_at', $actual);
    }

    public function testGetRowUrl()
    {
        $acl = $this->getModelMock('sheep_subscription/adminhtml_acl', array('canViewSubscriptionDetails'));
        $acl->expects($this->once())->method('canViewSubscriptionDetails')->willReturn(true);
        $this->replaceByMock('singleton', 'sheep_subscription/adminhtml_acl', $acl);


        $this->block->expects($this->once())->method('getUrl')->with('*/*/view', array('subscription_id' => 200));
        $this->block->getRowUrl(new Varien_Object(array('id' => 200)));
    }

    public function testGetRowUrlWithoutPermissions()
    {
        $acl = $this->getModelMock('sheep_subscription/adminhtml_acl', array('canViewSubscriptionDetails'));
        $acl->expects($this->once())->method('canViewSubscriptionDetails')->willReturn(false);
        $this->replaceByMock('singleton', 'sheep_subscription/adminhtml_acl', $acl);


        $this->block->expects($this->never())->method('getUrl')->with('*/*/view', array('subscription_id' => 200));
        $actual = $this->block->getRowUrl(new Varien_Object(array('id' => 200)));
        $this->assertEquals('#', $actual);
    }

    public function testGetGridUrl()
    {
        $this->block->expects($this->atLeast(2))->method('getUrl')->with('*/*/grid');
        $this->block->getGridUrl();
        $this->block->getAbsoluteGridUrl();
    }

    public function testTypeInfo()
    {
        $subscription = $this->getModelMock('sheep_subscription/subscription', array('load', 'save'));
        $subscription->setTypeId(300);
        $subscription->setTypeInfo(array('title' => '2 weeks'));

        $this->block->expects($this->once())->method('getUrl')->with('adminhtml/subscriptionType/edit', array('id' => 300));
        $actual = $this->block->typeInfo(300, $subscription);
        $this->assertContains('2 weeks', $actual);
        $this->assertContains('a href', $actual);
    }

    public function testTypeInfoWithout()
    {
        $subscription = $this->getModelMock('sheep_subscription/subscription', array('load', 'save'));
        $subscription->setTypeId(300);
        $subscription->setTypeInfo(array('title' => '2 weeks'));

        $this->block->expects($this->never())->method('getUrl');
        $actual = $this->block->typeInfo('', $subscription);
        $this->assertEquals('', $actual);
    }

    public function testCustomerPageUrl()
    {
        $subscription = $this->getModelMock('sheep_subscription/subscription', array('load', 'save'));
        $subscription->setCustomerId(199);

        $this->block->expects($this->once())->method('getUrl')->with('adminhtml/customer/edit', array('id' => 199));
        $actual = $this->block->customerPageUrl('customer@example.com', $subscription);
        $this->assertContains('customer@example.com', $actual);
        $this->assertContains('a href', $actual);
    }

    public function testCustomerEmailFilterWithValue()
    {
        $subscriptions = $this->getResourceModelMock('sheep_subscription/subscription_collection', array('addCustomerEmailFilter', 'load', 'save'));
        $subscriptions->expects($this->once())->method('addCustomerEmailFilter')->with('customer@example.com')->willReturnSelf();
        $this->replaceByMock('resource_model', 'sheep_subscription/subscription_collection', $subscriptions);

        $column = $this->getBlockMock('adminhtml/widget_grid_column', array('getFilter'));
        $column->expects($this->once())->method('getFilter')->willReturn(new Varien_Object(array('value' => 'customer@example.com')));

        $this->block->customerEmailFilter($subscriptions, $column);
    }

    public function testCustomerEmailFilterWithoutValue()
    {
        $subscriptions = $this->getResourceModelMock('sheep_subscription/subscription_collection', array('addCustomerEmailFilter', 'load', 'save'));
        $subscriptions->expects($this->never())->method('addCustomerEmailFilter')->with('customer@example.com')->willReturnSelf();
        $this->replaceByMock('resource_model', 'sheep_subscription/subscription_collection', $subscriptions);

        $column = $this->getBlockMock('adminhtml/widget_grid_column', array('getFilter'));
        $column->expects($this->once())->method('getFilter')->willReturn(new Varien_Object(array('value' => '')));

        $this->block->customerEmailFilter($subscriptions, $column);
    }

    public function testQuoteSubtotalFilterWithoutValue()
    {
        $subscriptions = $this->getResourceModelMock('sheep_subscription/subscription_collection', array('addQuoteSubtotalFilter', 'load', 'save'));
        $subscriptions->expects($this->never())->method('addQuoteSubtotalFilter');
        $this->replaceByMock('resource_model', 'sheep_subscription/subscription_collection', $subscriptions);

        $column = $this->getBlockMock('adminhtml/widget_grid_column', array('getFilter'));
        $column->expects($this->once())->method('getFilter')->willReturn(new Varien_Object());

        $this->block->quoteSubtotalFilter($subscriptions, $column);
    }

    public function testQuoteSubtotalFilterWithFromValue()
    {
        $subscriptions = $this->getResourceModelMock('sheep_subscription/subscription_collection', array('addQuoteSubtotalFilter', 'load', 'save'));
        $subscriptions->expects($this->once())->method('addQuoteSubtotalFilter')->with(10)->willReturnSelf();
        $this->replaceByMock('resource_model', 'sheep_subscription/subscription_collection', $subscriptions);

        $column = $this->getBlockMock('adminhtml/widget_grid_column', array('getFilter'));
        $column->expects($this->once())->method('getFilter')->willReturn(new Varien_Object(array(
                'value' => array('from' => 10)
            )
        ));

        $this->block->quoteSubtotalFilter($subscriptions, $column);
    }

    public function testQuoteSubtotalFilterWithToValue()
    {
        $subscriptions = $this->getResourceModelMock('sheep_subscription/subscription_collection', array('addQuoteSubtotalFilter', 'load', 'save'));
        $subscriptions->expects($this->once())->method('addQuoteSubtotalFilter')->with(null, 50)->willReturnSelf();
        $this->replaceByMock('resource_model', 'sheep_subscription/subscription_collection', $subscriptions);

        $column = $this->getBlockMock('adminhtml/widget_grid_column', array('getFilter'));
        $column->expects($this->once())->method('getFilter')->willReturn(new Varien_Object(array(
                'value' => array('to' => 50)
            )
        ));

        $this->block->quoteSubtotalFilter($subscriptions, $column);
    }

    public function testQuoteSubtotalFilterWithBothValues()
    {
        $subscriptions = $this->getResourceModelMock('sheep_subscription/subscription_collection', array('addQuoteSubtotalFilter', 'load', 'save'));
        $subscriptions->expects($this->at(0))->method('addQuoteSubtotalFilter')->with(20)->willReturnSelf();
        $subscriptions->expects($this->at(1))->method('addQuoteSubtotalFilter')->with(null, 70)->willReturnSelf();
        $this->replaceByMock('resource_model', 'sheep_subscription/subscription_collection', $subscriptions);

        $column = $this->getBlockMock('adminhtml/widget_grid_column', array('getFilter'));
        $column->expects($this->once())->method('getFilter')->willReturn(new Varien_Object(array(
                'value' => array('from' => 20, 'to' => 70)
            )
        ));

        $this->block->quoteSubtotalFilter($subscriptions, $column);
    }
}
