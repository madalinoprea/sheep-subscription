<?php

/**
 * Class Sheep_Subscription_Test_Block_History
 *
 * @category Sheep
 * @package  Sheep_Subscriptions
 * @license  Copyright: Mario Oprea, {2015}, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_Block_History
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Block_History extends EcomDev_PHPUnit_Test_Case
{

    /**
     * Tests that customer id is retrieved from customer/session
     */
    public function testGetCustomerId()
    {
        $sessionMock = $this->mockSession('customer/session', array('getCustomerId', 'init'));
        $sessionMock->expects($this->any())->method('getCustomerId')->willReturn(1000);
        $this->replaceByMock('singleton', 'customer/session', $sessionMock);

        /** @var Sheep_Subscription_Block_History $object */
        $object = $this->getBlockMock('sheep_subscription/history', array('toHtml'));
        $customerId = $object->getCustomerId();
        $this->assertEquals(1000, $customerId);
    }


    /**
     * Tests that associated subscriptions are retrieved from sheep_subscription/susbcription helper and this value are
     * cached (only one call is made to the helper)
     * customer id is passed
     */
    public function testGetSubscriptions()
    {
        $subscriptions = $this->getResourceModelMock('sheep_subscription/subscription_collection', array('load', 'addNextRenewalDate', 'addOrder'));
        $subscriptions->expects($this->once())->method('addNextRenewalDate');
        $subscriptions->expects($this->atLeast(2))->method('addOrder');

        $helperMock = $this->getHelperMock('sheep_subscription/subscription', array('getCustomerSubscriptions'));
        $helperMock->expects($this->once())->method('getCustomerSubscriptions')->with($this->equalTo(1000))->willReturn($subscriptions);
        $this->replaceByMock('helper', 'sheep_subscription/subscription', $helperMock);

        /** @var Sheep_Subscription_Block_History $object */
        $object = $this->getBlockMock('sheep_subscription/history', array('getCustomerId'));
        $object->expects($this->any())->method('getCustomerId')->will($this->returnValue(1000));

        $actual = $object->getSubscriptions();
        $this->assertEquals($subscriptions, $actual);

        // Consecutive calls are cached - helper is executed only once
        $actual2 = $object->getSubscriptions();
        $this->assertEquals($subscriptions, $actual2);
    }

    /**
     * Tests that pager block is added to layout and that pager's collection is initialized with block subscriptions
     */
    public function testPrepareLayout()
    {
        $subscriptions = array('a', 'b');
        $pagerMock = $this->getBlockMock('page/html_pager', array('setCollection'));
        $pagerMock->expects($this->once())->method('setCollection')->with($this->equalTo($subscriptions));

        $layoutMock = $this->getModelMock('core/layout', array('createBlock'));
        $layoutMock->expects($this->once())->method('createBlock')
            ->with('page/html_pager', 'sheep.subscriptions.history.pager')
            ->will($this->returnValue($pagerMock));

        /** @var Sheep_Subscription_Block_History $object */
        $object = $this->getBlockMock('sheep_subscription/history', array('getLayout', 'getSubscriptions'));
        $object->expects($this->any())->method('getLayout')->will($this->returnValue($layoutMock));
        $object->expects($this->any())->method('getSubscriptions')->will($this->returnValue($subscriptions));

        EcomDev_Utils_Reflection::invokeRestrictedMethod($object, '_prepareLayout');
        $actual = $object->getChild('pager');
        $this->assertNotFalse($actual);
    }

    /**
     * Tests that pager content is retrieved from pager block
     */
    public function testGetPagerHtml()
    {

        $object = $this->getBlockMock('sheep_subscription/history', array('getChildHtml'));
        $object->expects($this->once())->method('getChildHtml')->with('pager')->will($this->returnValue('pager html content'));
        $actual = $object->getPagerHtml();
        $this->assertEquals('pager html content', $actual);
    }

    /**
     * Tests that subscription id is passed to getUrl
     */
    public function testGetViewUrl()
    {
        $helperMock = $this->getHelperMock('sheep_subscription', array('getSubscriptionUrl'));
        $helperMock->expects($this->once())->method('getSubscriptionUrl')->with(100);
        $this->replaceByMock('helper', 'sheep_subscription', $helperMock);

        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getId'));
        $subscription->expects($this->once())->method('getId')->willReturn(100);

        $object = $this->getBlockMock('sheep_subscription/history', array('getUrl'));
        $object->getViewUrl($subscription);
    }
}
