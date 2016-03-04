<?php

/**
 * Class Sheep_Subscription_Test_Helper_Data
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, {2015}, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_Helper_Data
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Helper_Data extends EcomDev_PHPUnit_Test_Case
{
    protected $helper;

    protected function setUp()
    {
        parent::setUp();
        $this->helper = $this->getHelperMock('sheep_subscription', array('_getUrl'));
    }


    public function testGetSubscriptionListUrl()
    {
        $this->helper->expects($this->once())->method('_getUrl')->with('subscriptions/index/index');
        $this->helper->getSubscriptionListUrl();
    }

    public function testGetSubscriptionUrl()
    {
        $this->helper->expects($this->once())->method('_getUrl')->with('subscriptions/index/view', array('subscription_id' => 122));
        $this->helper->getSubscriptionUrl(122);
    }

    public function testGetSubscriptionUrlInStore()
    {
        $this->helper->expects($this->once())->method('_getUrl')->with('subscriptions/index/view',
            array('subscription_id' => 201, '_store' => 10)
        );
        $this->helper->getSubscriptionUrlInStore(201, 10);
    }

    public function testPauseSubscriptionUrl()
    {
        $this->helper->expects($this->once())->method('_getUrl')->with('subscriptions/service/pause', array('subscription_id' => 201));
        $this->helper->getPauseSubscriptionUrl(201);
    }

    public function testResumeSubscriptionUrl()
    {
        $this->helper->expects($this->once())->method('_getUrl')->with('subscriptions/service/resume', array('subscription_id' => 201));
        $this->helper->getResumeSubscriptionUrl(201);
    }

    public function testCancelSubscriptionUrl()
    {
        $this->helper->expects($this->once())->method('_getUrl')->with('subscriptions/service/cancel', array('subscription_id' => 201));
        $this->helper->getCancelSubscriptionUrl(201);

    }

}
