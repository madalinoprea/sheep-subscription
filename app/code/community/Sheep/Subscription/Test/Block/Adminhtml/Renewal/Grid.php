<?php

/**
 * Class Sheep_Subscription_Test_Block_Adminhtml_Renewal_Grid
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, {2015}, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_Block_Adminhtml_Renewal_Grid
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Block_Adminhtml_Renewal_Grid extends EcomDev_PHPUnit_Test_Case
{
    /** @var Sheep_Subscription_Block_Adminhtml_Renewal_Grid $block */
    protected $block;

    protected function setUp()
    {
        $this->replaceByMock('singleton', 'adminhtml/session', $this->getModelMock('admin/session', array('init')));

        $this->block = $this->getBlockMock('sheep_subscription/adminhtml_renewal_grid', array('getUrl', 'getLayout'));

        $layoutMock = $this->getModelMock('core/layout', array('createBlock'));
        $layoutMock->expects($this->any())->method('createBlock')->will($this->returnValue($this->getBlockMock('core/text', array('toHtml'))));
        $this->block->expects($this->any())->method('getLayout')->will($this->returnValue($layoutMock));
    }

    public function testConstruct()
    {
        $this->assertEquals('renewal_grid', $this->block->getId());
        $this->assertEquals('date',  EcomDev_Utils_Reflection::getRestrictedPropertyValue($this->block, '_defaultSort'));
        $this->assertEquals('DESC',  EcomDev_Utils_Reflection::getRestrictedPropertyValue($this->block, '_defaultDir'));
        $this->assertTrue(EcomDev_Utils_Reflection::getRestrictedPropertyValue($this->block, '_saveParametersInSession'));
        $this->assertTrue($this->block->getUseAjax());
    }

    public function testPrepareCollection()
    {
        // Assert that customer filter is set and quote data is added
        $renewals = $this->getResourceModelMock('sheep_subscription/renewal_collection', array('addSubscriptionData', 'addSubscriptionQuoteData', 'load', 'save'));
        $renewals->expects($this->once())->method('addSubscriptionData')->with(array('quote_id', 'start_date'))->willReturnSelf();
        $renewals->expects($this->once())->method('addSubscriptionQuoteData')->with(array('quote_currency_code', 'customer_email', 'subtotal'))->willReturnSelf();
        $this->replaceByMock('resource_model', 'sheep_subscription/renewal_collection', $renewals);

        EcomDev_Utils_Reflection::invokeRestrictedMethod($this->block, '_prepareCollection');
        $actual = $this->block->getCollection();
        $this->assertNotNull($actual);
        $this->assertInstanceOf('Sheep_Subscription_Model_Resource_Renewal_Collection', $actual);
    }

    public function testPrepareColumns()
    {
        EcomDev_Utils_Reflection::invokeRestrictedMethod($this->block, '_prepareColumns');
        $actual = $this->block->getColumns();

        $this->assertNotEmpty($actual);
        $this->assertArrayHasKey('id', $actual);
        $this->assertArrayHasKey('date', $actual);
        $this->assertArrayHasKey('customer_email', $actual);
        $this->assertArrayHasKey('subscription_id', $actual);
        $this->assertArrayHasKey('subtotal', $actual);
        $this->assertArrayHasKey('order', $actual);
        $this->assertArrayHasKey('status', $actual);
    }

    public function testGetRowUrl()
    {
        $actual = $this->block->getRowUrl(new Varien_Object());
        $this->assertFalse($actual);
    }

    public function testGetGridUrl()
    {
        $this->block->expects($this->once())->method('getUrl')->with('*/*/grid');
        $this->block->getGridUrl();
    }

    public function testSubscriptionUrl()
    {
        $row = Mage::getModel('sheep_subscription/renewal');
        $row->setSubscriptionId(200);

        $this->block->expects($this->once())->method('getUrl')->with('adminhtml/subscription/view', array('subscription_id' => 200))->willReturn('subscription_url');
        $actual = $this->block->subscriptionUrl(200, $row);

        $this->assertContains('a href=\'subscription_url\'', $actual);
        $this->assertContains('200', $actual);
    }

    public function testOrderUrl()
    {
        $row = Mage::getModel('sheep_subscription/renewal');
        $row->setOrderId(300);

        $this->block->expects($this->once())->method('getUrl')->with('adminhtml/sales_order/view', array('order_id' => 300))->willReturn('order_url');
        $actual = $this->block->orderUrl(300, $row);

        $this->assertContains('a href=\'order_url\'', $actual);
        $this->assertContains('300', $actual);
    }
}

