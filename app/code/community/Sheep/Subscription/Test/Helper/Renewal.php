<?php

/**
 * Class Sheep_Subscription_Test_Helper_Renewal
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, {2015}, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers   Sheep_Subscription_Helper_Renewal
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Helper_Renewal extends EcomDev_PHPUnit_Test_Case
{
    /** @var Sheep_Subscription_Helper_Renewal */
    protected $helper;

    protected function setUp()
    {
        parent::setUp();
        $this->helper = Mage::helper('sheep_subscription/renewal');
    }

    public function testGetStatusOptions()
    {
        $actual = $this->helper->getStatusOptions();

        $this->assertNotEmpty($actual);
        $this->assertArrayHasKey(10, $actual);
        $this->assertEquals('Pending', $actual[10]);
        $this->assertArrayHasKey(20, $actual);
        $this->assertEquals('Processing', $actual[20]);
        $this->assertArrayHasKey(25, $actual);
        $this->assertEquals('Waiting', $actual[25]);
        $this->assertArrayHasKey(30, $actual);
        $this->assertEquals('Payed', $actual[30]);
        $this->assertArrayHasKey(50, $actual);
        $this->assertEquals('Failed', $actual[50]);
    }


    public function testGetRenewal()
    {
        $type = $this->getModelMock('sheep_subscription/type', array('getNextRenewalDate'));
        $type->expects($this->once())->method('getNextRenewalDate')->with('2015-12-02')->willReturn('2016-02-02');

        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getId', 'getType'));
        $subscription->expects($this->any())->method('getId')->willReturn(100);
        $subscription->expects($this->any())->method('getType')->willReturn($type);

        $renewal = $this->getModelMock('sheep_subscription/renewal', array('setSubscriptionId', 'setStatus', 'setDate', 'save'));
        $renewal->expects($this->once())->method('setSubscriptionId')->with(100);
        $renewal->expects($this->once())->method('setStatus')->with(10); // Pending
        $renewal->expects($this->once())->method('setDate')->with('2016-02-02');
        $renewal->expects($this->never())->method('save');
        $this->replaceByMock('model', 'sheep_subscription/renewal', $renewal);

        $this->helper->getRenewal($subscription, '2015-12-02');
    }


    public function testGetCustomerRenewals()
    {
        $renewals = $this->getResourceModelMock('sheep_subscription/renewal_collection', array('addCustomerFilter', 'load'));
        $renewals->expects($this->once())->method('addCustomerFilter')->with(100);
        $renewals->expects($this->never())->method('load');
        $this->replaceByMock('resource_model', 'sheep_subscription/renewal_collection', $renewals);

        $actual = $this->helper->getCustomerRenewals(100);
        $this->assertEquals($renewals, $actual);
    }

    public function testGetUpcomingRenewalDateThreshold()
    {
        $helper = $this->getHelperMock('sheep_subscription/renewal', array('getDaysBeforeRenewal'));
        $helper->expects($this->any())->method('getDaysBeforeRenewal')->willReturn(7);

        $actual = $helper->getUpcomingRenewalDateThreshold('2016-02-03');
        $this->assertEquals('2016-02-10 00:00:00', $actual);
    }
}
