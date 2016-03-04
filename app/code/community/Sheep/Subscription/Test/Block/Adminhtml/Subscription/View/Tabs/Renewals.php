<?php

/**
 * Class Sheep_Subscription_Test_Block_Adminhtml_Subscription_View_Tabs_Renewals
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, {2015}, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_Block_Adminhtml_Subscription_View_Tabs_Renewals
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Block_Adminhtml_Subscription_View_Tabs_Renewals extends EcomDev_PHPUnit_Test_Case
{

    /** @var Sheep_Subscription_Block_Adminhtml_Subscription_View_Tabs_Renewals $block */
    protected $block;

    protected function setUp()
    {
        parent::setUp();
        $this->replaceByMock('singleton', 'adminhtml/session', $this->getModelMock('admin/session', array('init')));

        $this->block = $this->getBlockMock('sheep_subscription/adminhtml_subscription_view_tabs_renewals', array('getUrl', 'getLayout', '_preparePage', '_afterLoadCollection'));

        $layoutMock = $this->getModelMock('core/layout', array('createBlock'));
        $layoutMock->expects($this->any())->method('createBlock')->will($this->returnValue($this->getBlockMock('core/text', array('toHtml'))));
        $this->block->expects($this->any())->method('getLayout')->will($this->returnValue($layoutMock));
    }

    public function testGetSubscriptionId()
    {
        $this->block->setSubscriptionId(120);
        $actual = $this->block->getSubscriptionId();
        $this->assertEquals(120, $actual);
    }

    public function testPrepareCollection()
    {
        // Assert that customer filter is set and quote data is added
        $renewals = $this->getResourceModelMock('sheep_subscription/renewal_collection', array('addSubscriptionFilter', 'addSubscriptionData', 'addSubscriptionQuoteData', 'load', 'save'));
        $renewals->expects($this->once())->method('addSubscriptionFilter')->with(100)->willReturnSelf();
        $renewals->expects($this->once())->method('addSubscriptionData')->with(array('quote_id', 'start_date'))->willReturnSelf();
        $renewals->expects($this->once())->method('addSubscriptionQuoteData')->with(array('quote_currency_code', 'customer_email', 'subtotal'))->willReturnSelf();
        $this->replaceByMock('resource_model', 'sheep_subscription/renewal_collection', $renewals);

        $this->block->setSubscriptionId(100);
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
        $this->assertArrayHasKey('subtotal', $actual);
        $this->assertArrayHasKey('order', $actual);
        $this->assertArrayHasKey('status', $actual);

        $this->assertArrayNotHasKey('subscription_id', $actual);
        $this->assertArrayNotHasKey('customer_email', $actual);
    }

    public function testGetGridUrl()
    {
        $this->block->expects($this->once())->method('getUrl')->with('*/*/renewalsTab');
        $this->block->getGridUrl();
    }
}

