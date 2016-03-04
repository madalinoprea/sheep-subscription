<?php

/**
 * Class Sheep_Subscription_Test_Controller_IndexController
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_IndexController
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Controller_IndexController extends Sheep_Util_Test_Case_Controller
{
    /** @var Sheep_Subscription_IndexController $controller */
    protected $controller;

    protected function setUp()
    {
        parent::setUp();
        Mage::unregister('pss_subscription');
        $this->controller = $this->getControllerInstance('Sheep_Subscription_IndexController', array('_initSubscription', '_initLayoutMessages', 'getLayout', 'loadLayout', 'renderLayout'));
    }

    protected function tearDown()
    {
        Mage::unregister('pss_subscription');
        parent::tearDown();
    }

    public function testIndexAction()
    {
        $this->controller->expects($this->never())->method('_initSubscription');
        $this->controller->expects($this->once())->method('loadLayout');
        $this->controller->expects($this->once())->method('_initLayoutMessages')->with('customer/session');
        $this->controller->expects($this->once())->method('renderLayout');

        $this->controller->indexAction();
    }

    public function testViewAction()
    {
        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getId'));
        $subscription->expects($this->any())->method('getId')->willReturn(100);

        $this->controller->expects($this->once())->method('_initSubscription')->willReturn($subscription);
        $this->controller->expects($this->once())->method('_initLayoutMessages')->with('customer/session');

        $this->controller->viewAction();

        $this->assertEquals($subscription, Mage::registry('pss_subscription'));
    }

    public function testViewActionWithoutSubscription()
    {
        $controller = $this->getControllerInstance('Sheep_Subscription_IndexController', array('_initSubscription', '_redirect', 'loadLayout', 'renderLayout'));
        $controller->expects($this->once())->method('_initSubscription')->willReturn(null);
        $controller->expects($this->once())->method('_redirect')->with('subscriptions/index/index');
        $controller->expects($this->never())->method('loadLayout');
        $controller->expects($this->never())->method('renderLayout');

        $controller->viewAction();
    }

}
