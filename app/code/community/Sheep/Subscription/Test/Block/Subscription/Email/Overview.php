<?php

/**
 * Class Sheep_Subscription_Test_Block_Subscription_Email_Overview
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers   Sheep_Subscription_Block_Subscription_Email_Overview
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Block_Subscription_Email_Overview extends EcomDev_PHPUnit_Test_Case
{
    /** @var Sheep_Subscription_Block_Subscription_Email_Overview $block */
    protected $block;

    protected function setUp()
    {
        parent::setUp();
        $this->block = $this->getBlockMock('sheep_subscription/subscription_email_overview', array('toHtml'));
    }


    public function testGetCustomer()
    {
        $block = $this->getBlockMock('sheep_subscription/subscription_email_overview', array('getData'));
        $block->expects($this->once())->method('getData')->with('customer');
        $block->getCustomer();
    }


    public function testGetSubscriptionUrl()
    {
        $block = $this->getBlockMock('sheep_subscription/subscription_email_overview', array('getCustomer'));
        $block->expects($this->once())->method('getCustomer')->willReturn(
            new Varien_Object(array('store' => new Varien_Object(array('id' => 10))))
        );
        $helper = $this->getHelperMock('sheep_subscription', array('getSubscriptionUrlInStore'));
        $helper->expects($this->once())->method('getSubscriptionUrlInStore')->with(201, 10);
        $this->replaceByMock('helper', 'sheep_subscription', $helper);

        $block->getSubscriptionUrl(201);
    }


    public function testGetUpcomingRenewals()
    {
        $helper = $this->getHelperMock('sheep_subscription/renewal', array('getUpcomingRenewalDateThreshold'));
        $helper->expects($this->any())->method('getUpcomingRenewalDateThreshold')->willReturn('some date');
        $this->replaceByMock('helper', 'sheep_subscription/renewal', $helper);

        $renewals = $this->getResourceModelMock('sheep_subscription/renewal_collection', array('addCustomerFilter', 'addStatusFilter', 'addFieldToFilter', 'addOrder'));
        $renewals->expects($this->once())->method('addCustomerFilter')->with(101);
        $renewals->expects($this->once())->method('addStatusFilter')->with(
            array('in' => array(Sheep_Subscription_Model_Renewal::STATUS_PENDING, Sheep_Subscription_Model_Renewal::STATUS_PROCESSING, Sheep_Subscription_Model_Renewal::STATUS_WAITING))
        );
        $renewals->expects($this->once())->method('addFieldToFilter')->with('date', array('to' => 'some date', 'datetime' => true));
        $renewals->expects($this->once())->method('addOrder')->with('date', 'ASC');

        $this->replaceByMock('resource_model', 'sheep_subscription/renewal_collection', $renewals);

        $block = $this->getBlockMock('sheep_subscription/subscription_email_overview', array('getCustomer'));
        $block->expects($this->once())->method('getCustomer')->willReturn(
            new Varien_Object(array('id' => 101))
        );

        $actual = $block->getUpcomingRenewals();
        $this->assertNotNull($actual);
        $this->assertEquals($renewals, $actual);

        // Consecutive calls are cached
        $actual = $block->getUpcomingRenewals();
        $this->assertEquals($renewals, $actual);
    }


    public function testGetFailingRenewals()
    {
        $renewals = $this->getResourceModelMock('sheep_subscription/renewal_collection', array('addStatusFilter', 'addFieldToFilter'));
        $renewals->expects($this->once())->method('addStatusFilter')->with(Sheep_Subscription_Model_Renewal::STATUS_PROCESSING);
        $renewals->expects($this->once())->method('addFieldToFilter')->with('failed_payments_count', array('gt' => 0));

        $helper = $this->getHelperMock('sheep_subscription/renewal', array('getCustomerRenewals'));
        $helper->expects($this->once())->method('getCustomerRenewals')->with(101)->willReturn($renewals);
        $this->replaceByMock('helper', 'sheep_subscription/renewal', $helper);

        $this->block->setCustomer(new Varien_Object(array('id' => 101)));
        $actual = $this->block->getFailingRenewals();
        $this->assertNotNull($actual);
        $this->assertEquals($renewals, $actual);

        $actual = $this->block->getFailingRenewals();
        $this->assertNotNull($actual);
        $this->assertEquals($renewals, $actual);
    }


    public function testGetActiveSubscription()
    {
        $subscriptions = $this->getResourceModelMock('sheep_subscription/subscription_collection', array('addNextRenewalDate'));
        $subscriptions->expects($this->once())->method('addNextRenewalDate');

        $helper = $this->getHelperMock('sheep_subscription/subscription', array('getCustomerActiveSubscriptions'));
        $helper->expects($this->once())->method('getCustomerActiveSubscriptions')->with(201)->willReturn($subscriptions);
        $this->replaceByMock('helper', 'sheep_subscription/subscription', $helper);

        $this->block->setCustomer(new Varien_Object(array('id' => 201)));
        $actual = $this->block->getActiveSubscriptions();
        $this->assertNotNull($actual);
        $this->assertEquals($subscriptions, $actual);

        $actual = $this->block->getActiveSubscriptions();
        $this->assertEquals($subscriptions, $actual);
    }


    public function testGetInactiveSubscriptions()
    {
        $subscriptions = $this->getResourceModelMock('sheep_subscription/subscription_collection', array('addStatusFilter'));
        $subscriptions->expects($this->once())->method('addStatusFilter')->with(
            array('neq' => Sheep_Subscription_Model_Subscription::STATUS_ACTIVE)
        );

        $helper = $this->getHelperMock('sheep_subscription/subscription', array('getCustomerSubscriptions'));
        $helper->expects($this->once())->method('getCustomerSubscriptions')->with(202)->willReturn($subscriptions);
        $this->replaceByMock('helper', 'sheep_subscription/subscription', $helper);

        $this->block->setCustomer(new Varien_Object(array('id' => 202)));
        $actual = $this->block->getInactiveSubscriptions();
        $this->assertNotNull($actual);
        $this->assertEquals($subscriptions, $actual);

        $actual = $this->block->getInactiveSubscriptions();
        $this->assertEquals($subscriptions, $actual);
    }


    public function testGetExpiredPayments()
    {
        $helper = $this->getHelperMock('sheep_subscription/payment', array('getExpirationDateThreshold'));
        $helper->expects($this->any())->method('getExpirationDateThreshold')->willReturn('2016-02-14');
        $this->replaceByMock('helper', 'sheep_subscription/payment', $helper);

        $payments = $this->getResourceModelMock('sheep_subscription/payment_collection', array('addFieldToFilter', 'load'));
        $payments->expects($this->once())->method('addFieldToFilter')->with('customer_id', 101);

        $service = $this->getModelMock('sheep_subscription/notification_service', array('getExpiredPayments'));
        $service->expects($this->once())->method('getExpiredPayments')->with('2016-02-14')->willReturn($payments);
        $this->replaceByMock('model', 'sheep_subscription/notification_service', $service);

        $this->block->setCustomer(new Varien_Object(array('id' => 101)));
        $actual = $this->block->getExpiredPayments();
        $this->assertNotNull($actual);
        $this->assertEquals($payments, $actual);

        // Check that collection is loaded only once
        $actual = $this->block->getExpiredPayments();
        $this->assertEquals($payments, $actual);
    }

}
