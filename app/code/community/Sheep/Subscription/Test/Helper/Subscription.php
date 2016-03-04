<?php

/**
 * Class Sheep_Subscription_Test_Helper_Subscription
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, {2015}, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_Helper_Subscription
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Helper_Subscription extends EcomDev_PHPUnit_Test_Case
{

    public function testGetStatusOptions()
    {
        $helper = Mage::helper('sheep_subscription/subscription');
        $actual = $helper->getStatusOptions();

        $this->assertNotEmpty($actual);
        $this->assertArrayHasKey(10, $actual);
        $this->assertEquals('Active', $actual[10]);
        $this->assertArrayHasKey(20, $actual);
        $this->assertEquals('Paused', $actual[20]);
        $this->assertArrayHasKey(30, $actual);
        $this->assertEquals('Cancelled', $actual[30]);
        $this->assertArrayHasKey(50, $actual);
        $this->assertEquals('Expired', $actual[50]);
    }


    public function testGetCustomerSubscription()
    {
        $subscriptionsMock = $this->getResourceModelMock('sheep_subscription/subscription_collection', array('load', 'addCustomerFilter'));
        $subscriptionsMock->expects($this->once())->method('addCustomerFilter')->with(20);
        $this->replaceByMock('resource_model', 'sheep_subscription/subscription_collection', $subscriptionsMock);

        $helper = $this->getHelperMock('sheep_subscription/subscription', array('__'));
        $helper->getCustomerSubscriptions(20);
    }


    public function testGetCustomerActiveSubscriptions()
    {
        $subscriptions = $this->getResourceModelMock('sheep_subscription/subscription_collection', array('load', 'addStatusFilter'));
        $subscriptions->expects($this->never())->method('load');
        $subscriptions->expects($this->once())->method('addStatusFilter')->with(Sheep_Subscription_Model_Subscription::STATUS_ACTIVE)->willReturnSelf();

        $helper = $this->getHelperMock('sheep_subscription/subscription', array('getCustomerSubscriptions'));
        $helper->expects($this->once())->method('getCustomerSubscriptions')->with(100)->willReturn($subscriptions);

        $actual = $helper->getCustomerActiveSubscriptions(100);
        $this->assertEquals($subscriptions, $actual);
    }


    public function testGetCustomerRecurringProducts()
    {
        $select = $this->getMock('Varien_Db_Select', array('where'), array(), '', false);
        $select->expects($this->at(0))->method('where')->with('s.customer_id = ?', 120);
        $select->expects($this->at(1))->method('where')->with('s.status = ?', Sheep_Subscription_Model_Subscription::STATUS_ACTIVE);

        $quoteItems = $this->getResourceModelMock('sales/quote_item_collection', array('join', 'getSelect', '_initSelect'));
        $quoteItems->expects($this->at(0))->method('join')->with($this->arrayHasKey('q'), 'quote_id = q.entity_id');
        $quoteItems->expects($this->at(1))->method('join')->with($this->arrayHasKey('s'), 's.quote_id = q.entity_id');
        $quoteItems->expects($this->any())->method('getSelect')->willReturn($select);
        $this->replaceByMock('resource_model', 'sales/quote_item_collection', $quoteItems);

        $helper = $this->getHelperMock('sheep_subscription/subscription', array('__'));

        $actual = $helper->getCustomerRecurringProducts(120);
        $this->assertEquals($quoteItems, $actual);
    }

}
