<?php

/**
 * Class Sheep_Subscription_Test_Controller_Front_Action
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, {2015}, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_Controller_Front_Action
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Controller_Front_Action extends Sheep_Util_Test_Case_Controller
{
    protected $controller;

    protected function setUp()
    {
        parent::setUp();
        $this->controller = $this->getControllerInstance('Sheep_Subscription_Controller_Front_Action');
    }

    public function testGetCustomerId()
    {
        $sessionMock = $this->getModelMock('customer/session', array('getCustomerId', 'init'));
        $sessionMock->expects($this->once())->method('getCustomerId')->willReturn(100);
        $this->replaceByMock('singleton', 'customer/session', $sessionMock);

        $actual = EcomDev_Utils_Reflection::invokeRestrictedMethod($this->controller, '_getCustomerId');
        $this->assertEquals(100, $actual);
    }

    public function testPreDispatchWithAuthentication()
    {
        $sessionMock = $this->getModelMock('customer/session', array('authenticate', 'init'));
        $sessionMock->expects($this->once())->method('authenticate')->willReturn(true);
        $this->replaceByMock('singleton', 'customer/session', $sessionMock);

        $this->controller->preDispatch();
        $this->assertFalse($this->controller->getFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH));
    }

    public function testPreDispatchWithoutAuthentication()
    {
        $sessionMock = $this->getModelMock('customer/session', array('authenticate', 'init'));
        $sessionMock->expects($this->once())->method('authenticate')->willReturn(false);
        $this->replaceByMock('singleton', 'customer/session', $sessionMock);

        $this->controller->preDispatch();
        $this->assertTrue($this->controller->getFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH));
    }

    public function testInitSubscription()
    {
        $sessionMock = $this->getModelMock('customer/session', array('getCustomerId', 'init'));
        $sessionMock->expects($this->once())->method('getCustomerId')->willReturn(200);
        $this->replaceByMock('singleton', 'customer/session', $sessionMock);

        $subscription = $this->getModelMock('sheep_subscription/subscription', array('load', 'getId', 'getCustomerId'));
        $subscription->expects($this->once())->method('load')->with(100)->willReturnSelf();
        $subscription->expects($this->once())->method('getId')->willReturn(100);
        $subscription->expects($this->once())->method('getCustomerId')->willReturn(200);
        $this->replaceByMock('model', 'sheep_subscription/subscription', $subscription);

        $this->getRequest()->setParam('subscription_id', 100);
        $actual = EcomDev_Utils_Reflection::invokeRestrictedMethod($this->controller, '_initSubscription');
        $this->assertNotNull($actual);
    }

    public function testInitSubscriptionWithMissing()
    {
        $sessionMock = $this->getModelMock('customer/session', array('getCustomerId', 'init'));
        $sessionMock->expects($this->never())->method('getCustomerId');
        $this->replaceByMock('singleton', 'customer/session', $sessionMock);

        $subscription = $this->getModelMock('sheep_subscription/subscription', array('load', 'getId', 'getCustomerId'));
        $subscription->expects($this->once())->method('load')->with(100)->willReturnSelf();
        $subscription->expects($this->once())->method('getId')->willReturn(null);
        $subscription->expects($this->never())->method('getCustomerId')->willReturn(null);
        $this->replaceByMock('model', 'sheep_subscription/subscription', $subscription);

        $this->getRequest()->setParam('subscription_id', 100);
        $actual = EcomDev_Utils_Reflection::invokeRestrictedMethod($this->controller, '_initSubscription');
        $this->assertNull($actual);
    }

    public function testInitSubscriptionWithDifferentCustomer()
    {
        $sessionMock = $this->getModelMock('customer/session', array('getCustomerId', 'init'));
        $sessionMock->expects($this->once())->method('getCustomerId')->willReturn(300);
        $this->replaceByMock('singleton', 'customer/session', $sessionMock);

        $subscription = $this->getModelMock('sheep_subscription/subscription', array('load', 'getId', 'getCustomerId'));
        $subscription->expects($this->once())->method('load')->with(100)->willReturnSelf();
        $subscription->expects($this->once())->method('getId')->willReturn(100);
        $subscription->expects($this->once())->method('getCustomerId')->willReturn(200);
        $this->replaceByMock('model', 'sheep_subscription/subscription', $subscription);

        $this->getRequest()->setParam('subscription_id', 100);
        $actual = EcomDev_Utils_Reflection::invokeRestrictedMethod($this->controller, '_initSubscription');
        $this->assertNull($actual);
    }

}
