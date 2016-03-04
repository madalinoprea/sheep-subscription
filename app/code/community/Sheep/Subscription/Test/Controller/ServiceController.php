<?php

/**
 * Class Sheep_Subscription_Test_Controller_IndexController
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_ServiceController
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Controller_ServiceController extends Sheep_Util_Test_Case_Controller
{
    /** @var Sheep_Subscription_IndexController $controller */
    protected $controller;

    protected function setUp()
    {
        parent::setUp();
        $this->controller = $this->getControllerInstance('Sheep_Subscription_ServiceController', array('_initSubscription', '_initLayoutMessages', 'getLayout', 'loadLayout', 'renderLayout'));
    }

    protected function tearDown()
    {
        Mage::unregister('pss_subscription');
        parent::tearDown();
    }


    public function testGetService()
    {
        $actual = EcomDev_Utils_Reflection::invokeRestrictedMethod($this->controller, 'getService');
        $this->assertNotNull($actual);
        $this->assertInstanceOf('Sheep_Subscription_Model_Service', $actual);
    }


    /**
     * Verifies if this check is delegated to helper method
     */
    public function testIsManagementAllowed()
    {
        $helper = $this->getHelperMock('sheep_subscription', array('getIsAccountManagementAllowed'));
        $helper->expects($this->once())->method('getIsAccountManagementAllowed')->willReturn(false);
        $this->replaceByMock('helper', 'sheep_subscription', $helper);

        $result = $this->controller->isManagementAllowed();
        $this->assertFalse($result);
    }


    public function testPauseActionWithoutSubscription()
    {
        $service = $this->getModelMock('sheep_subscription/service', array('canBePaused', 'pauseSubscription'));
        $service->expects($this->never())->method('canBePaused');
        $service->expects($this->never())->method('pauseSubscription');

        $helper = $this->getHelperMock('sheep_subscription', array('getSubscriptionListUrl'));
        $helper->expects($this->any())->method('getSubscriptionListUrl')->willReturn('subscription list url');
        $this->replaceByMock('helper', 'sheep_subscription', $helper);

        $controller = $this->getControllerInstance('Sheep_Subscription_ServiceController', array('isManagementAllowed', 'getService', '_initSubscription', '_redirectUrl'));
        $controller->expects($this->any())->method('isManagementAllowed')->willReturn(true);
        $controller->expects($this->any())->method('getService')->willReturn($service);
        $controller->expects($this->once())->method('_initSubscription')->willReturn(null);
        $controller->expects($this->once())->method('_redirectUrl')->with('subscription list url');

        $controller->pauseAction();
    }


    public function testPauseActionWithPausedSubscription()
    {
        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getId'));
        $subscription->expects($this->any())->method('getId')->willReturn(100);

        $service = $this->getModelMock('sheep_subscription/service', array('canBePaused', 'pauseSubscription'));
        $service->expects($this->once())->method('canBePaused')->with($subscription)->willReturn(false);
        $service->expects($this->never())->method('pauseSubscription');

        $helper = $this->getHelperMock('sheep_subscription', array('getSubscriptionUrl'));
        $helper->expects($this->any())->method('getSubscriptionUrl')->willReturn('subscription view url');
        $this->replaceByMock('helper', 'sheep_subscription', $helper);

        $controller = $this->getControllerInstance('Sheep_Subscription_ServiceController', array('isManagementAllowed', 'getService', '_initSubscription', '_redirectUrl'));
        $controller->expects($this->any())->method('isManagementAllowed')->willReturn(true);
        $controller->expects($this->any())->method('getService')->willReturn($service);
        $controller->expects($this->once())->method('_initSubscription')->willReturn($subscription);
        $controller->expects($this->once())->method('_redirectUrl')->with('subscription view url');

        $controller->pauseAction();
    }


    public function testPauseSubscriptionNotAllowed()
    {
        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getId'));
        $subscription->expects($this->any())->method('getId')->willReturn(100);

        $service = $this->getModelMock('sheep_subscription/service', array('canBePaused', 'pauseSubscription'));
        $service->expects($this->never())->method('canBePaused');
        $service->expects($this->never())->method('pauseSubscription');

        $helper = $this->getHelperMock('sheep_subscription', array('getSubscriptionUrl', 'getSubscriptionListUrl'));
        $helper->expects($this->any())->method('getSubscriptionUrl')->willReturn('subscription view url');
        $helper->expects($this->any())->method('getSubscriptionListUrl')->willReturn('subscription list url');
        $this->replaceByMock('helper', 'sheep_subscription', $helper);

        $controller = $this->getControllerInstance('Sheep_Subscription_ServiceController', array('isManagementAllowed', 'getService', '_initSubscription', '_redirectUrl'));
        $controller->expects($this->any())->method('isManagementAllowed')->willReturn(false);
        $controller->expects($this->any())->method('getService')->willReturn($service);
        $controller->expects($this->once())->method('_initSubscription')->willReturn($subscription);
        $controller->expects($this->once())->method('_redirectUrl')->with('subscription list url');

        $controller->pauseAction();
    }


    public function testPauseAction()
    {
        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getId'));
        $subscription->expects($this->any())->method('getId')->willReturn(100);

        $service = $this->getModelMock('sheep_subscription/service', array('canBePaused', 'pauseSubscription'));
        $service->expects($this->once())->method('canBePaused')->with($subscription)->willReturn(true);
        $service->expects($this->once())->method('pauseSubscription')->with($subscription);

        $helper = $this->getHelperMock('sheep_subscription', array('getSubscriptionUrl'));
        $helper->expects($this->any())->method('getSubscriptionUrl')->willReturn('subscription view url');
        $this->replaceByMock('helper', 'sheep_subscription', $helper);

        $controller = $this->getControllerInstance('Sheep_Subscription_ServiceController', array('isManagementAllowed', 'getService', '_initSubscription', '_redirectUrl'));
        $controller->expects($this->any())->method('isManagementAllowed')->willReturn(true);
        $controller->expects($this->any())->method('getService')->willReturn($service);
        $controller->expects($this->once())->method('_initSubscription')->willReturn($subscription);
        $controller->expects($this->once())->method('_redirectUrl')->with('subscription view url');

        $controller->pauseAction();
    }


    public function testResumeActionWithoutSubscription()
    {
        $service = $this->getModelMock('sheep_subscription/service', array('canBeResumed', 'resumeSubscription'));
        $service->expects($this->never())->method('canBeResumed');
        $service->expects($this->never())->method('resumeSubscription');

        $helper = $this->getHelperMock('sheep_subscription', array('getSubscriptionListUrl'));
        $helper->expects($this->any())->method('getSubscriptionListUrl')->willReturn('subscription list url');
        $this->replaceByMock('helper', 'sheep_subscription', $helper);

        $controller = $this->getControllerInstance('Sheep_Subscription_ServiceController', array('isManagementAllowed', 'getService', '_initSubscription', '_redirectUrl'));
        $controller->expects($this->any())->method('isManagementAllowed')->willReturn(true);
        $controller->expects($this->any())->method('getService')->willReturn($service);
        $controller->expects($this->once())->method('_initSubscription')->willReturn(null);
        $controller->expects($this->once())->method('_redirectUrl')->with('subscription list url');

        $controller->resumeAction();
    }


    public function testResumeActionWithPausedSubscription()
    {
        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getId'));
        $subscription->expects($this->any())->method('getId')->willReturn(100);

        $service = $this->getModelMock('sheep_subscription/service', array('canBeResumed', 'resumeSubscription'));
        $service->expects($this->once())->method('canBeResumed')->with($subscription)->willReturn(false);
        $service->expects($this->never())->method('resumeSubscription');

        $helper = $this->getHelperMock('sheep_subscription', array('getSubscriptionUrl'));
        $helper->expects($this->any())->method('getSubscriptionUrl')->willReturn('subscription view url');
        $this->replaceByMock('helper', 'sheep_subscription', $helper);

        $controller = $this->getControllerInstance('Sheep_Subscription_ServiceController', array('isManagementAllowed', 'getService', '_initSubscription', '_redirectUrl'));
        $controller->expects($this->any())->method('isManagementAllowed')->willReturn(true);
        $controller->expects($this->any())->method('getService')->willReturn($service);
        $controller->expects($this->once())->method('_initSubscription')->willReturn($subscription);
        $controller->expects($this->once())->method('_redirectUrl')->with('subscription view url');

        $controller->resumeAction();
    }


    public function testResumeSubscriptionNotAllowed()
    {
        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getId'));
        $subscription->expects($this->any())->method('getId')->willReturn(100);

        $service = $this->getModelMock('sheep_subscription/service', array('canBePaused', 'pauseSubscription'));
        $service->expects($this->never())->method('canBeResumed');
        $service->expects($this->never())->method('resumeSubscription');

        $helper = $this->getHelperMock('sheep_subscription', array('getSubscriptionUrl', 'getSubscriptionListUrl'));
        $helper->expects($this->any())->method('getSubscriptionUrl')->willReturn('subscription view url');
        $helper->expects($this->any())->method('getSubscriptionListUrl')->willReturn('subscription list url');
        $this->replaceByMock('helper', 'sheep_subscription', $helper);

        $controller = $this->getControllerInstance('Sheep_Subscription_ServiceController', array('isManagementAllowed', 'getService', '_initSubscription', '_redirectUrl'));
        $controller->expects($this->any())->method('isManagementAllowed')->willReturn(false);
        $controller->expects($this->any())->method('getService')->willReturn($service);
        $controller->expects($this->once())->method('_initSubscription')->willReturn($subscription);
        $controller->expects($this->once())->method('_redirectUrl')->with('subscription list url');

        $controller->resumeAction();
    }


    public function testResumeAction()
    {
        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getId'));
        $subscription->expects($this->any())->method('getId')->willReturn(100);

        $service = $this->getModelMock('sheep_subscription/service', array('canBeResumed', 'resumeSubscription'));
        $service->expects($this->once())->method('canBeResumed')->with($subscription)->willReturn(true);
        $service->expects($this->once())->method('resumeSubscription')->with($subscription);

        $helper = $this->getHelperMock('sheep_subscription', array('getSubscriptionUrl'));
        $helper->expects($this->any())->method('getSubscriptionUrl')->willReturn('subscription view url');
        $this->replaceByMock('helper', 'sheep_subscription', $helper);

        $controller = $this->getControllerInstance('Sheep_Subscription_ServiceController', array('isManagementAllowed', 'getService', '_initSubscription', '_redirectUrl'));
        $controller->expects($this->any())->method('isManagementAllowed')->willReturn(true);
        $controller->expects($this->any())->method('getService')->willReturn($service);
        $controller->expects($this->once())->method('_initSubscription')->willReturn($subscription);
        $controller->expects($this->once())->method('_redirectUrl')->with('subscription view url');

        $controller->resumeAction();
    }


    public function testCancelActionWithoutSubscription()
    {
        $service = $this->getModelMock('sheep_subscription/service', array('canBeCancelled', 'cancelSubscription'));
        $service->expects($this->never())->method('canBeCancelled');
        $service->expects($this->never())->method('cancelSubscription');

        $helper = $this->getHelperMock('sheep_subscription', array('getSubscriptionListUrl'));
        $helper->expects($this->any())->method('getSubscriptionListUrl')->willReturn('subscription list url');
        $this->replaceByMock('helper', 'sheep_subscription', $helper);

        $controller = $this->getControllerInstance('Sheep_Subscription_ServiceController', array('isManagementAllowed', 'getService', '_initSubscription', '_redirectUrl'));
        $controller->expects($this->any())->method('isManagementAllowed')->willReturn(true);
        $controller->expects($this->any())->method('getService')->willReturn($service);
        $controller->expects($this->once())->method('_initSubscription')->willReturn(null);
        $controller->expects($this->once())->method('_redirectUrl')->with('subscription list url');

        $controller->cancelAction();
    }


    public function testCancelActionWithPausedSubscription()
    {
        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getId'));
        $subscription->expects($this->any())->method('getId')->willReturn(100);

        $service = $this->getModelMock('sheep_subscription/service', array('canBeCancelled', 'cancelSubscription'));
        $service->expects($this->once())->method('canBeCancelled')->with($subscription)->willReturn(false);
        $service->expects($this->never())->method('cancelSubscription');

        $helper = $this->getHelperMock('sheep_subscription', array('getSubscriptionUrl'));
        $helper->expects($this->any())->method('getSubscriptionUrl')->willReturn('subscription view url');
        $this->replaceByMock('helper', 'sheep_subscription', $helper);

        $controller = $this->getControllerInstance('Sheep_Subscription_ServiceController', array('isManagementAllowed', 'getService', '_initSubscription', '_redirectUrl'));
        $controller->expects($this->any())->method('isManagementAllowed')->willReturn(true);
        $controller->expects($this->any())->method('getService')->willReturn($service);
        $controller->expects($this->once())->method('_initSubscription')->willReturn($subscription);
        $controller->expects($this->once())->method('_redirectUrl')->with('subscription view url');

        $controller->cancelAction();
    }

    public function testCancelSubscriptionNotAllowed()
    {
        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getId'));
        $subscription->expects($this->any())->method('getId')->willReturn(100);

        $service = $this->getModelMock('sheep_subscription/service', array('canBePaused', 'pauseSubscription'));
        $service->expects($this->never())->method('canBeCancelled');
        $service->expects($this->never())->method('cancelSubscription');

        $helper = $this->getHelperMock('sheep_subscription', array('getSubscriptionUrl', 'getSubscriptionListUrl'));
        $helper->expects($this->any())->method('getSubscriptionUrl')->willReturn('subscription view url');
        $helper->expects($this->any())->method('getSubscriptionListUrl')->willReturn('subscription list url');
        $this->replaceByMock('helper', 'sheep_subscription', $helper);

        $controller = $this->getControllerInstance('Sheep_Subscription_ServiceController', array('isManagementAllowed', 'getService', '_initSubscription', '_redirectUrl'));
        $controller->expects($this->any())->method('isManagementAllowed')->willReturn(false);
        $controller->expects($this->any())->method('getService')->willReturn($service);
        $controller->expects($this->once())->method('_initSubscription')->willReturn($subscription);
        $controller->expects($this->once())->method('_redirectUrl')->with('subscription list url');

        $controller->cancelAction();
    }

    public function testCancelAction()
    {
        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getId'));
        $subscription->expects($this->any())->method('getId')->willReturn(100);

        $service = $this->getModelMock('sheep_subscription/service', array('canBeCancelled', 'cancelSubscription'));
        $service->expects($this->once())->method('canBeCancelled')->with($subscription)->willReturn(true);
        $service->expects($this->once())->method('cancelSubscription')->with($subscription);

        $helper = $this->getHelperMock('sheep_subscription', array('getSubscriptionUrl'));
        $helper->expects($this->any())->method('getSubscriptionUrl')->willReturn('subscription view url');
        $this->replaceByMock('helper', 'sheep_subscription', $helper);

        $controller = $this->getControllerInstance('Sheep_Subscription_ServiceController', array('isManagementAllowed', 'getService', '_initSubscription', '_redirectUrl'));
        $controller->expects($this->any())->method('isManagementAllowed')->willReturn(true);
        $controller->expects($this->any())->method('getService')->willReturn($service);
        $controller->expects($this->once())->method('_initSubscription')->willReturn($subscription);
        $controller->expects($this->once())->method('_redirectUrl')->with('subscription view url');

        $controller->cancelAction();
    }

    public function renewalDatesProvider()
    {
        return array(
            array('2015-12-01', '2015-12-01 12:00:00'),
            array('2015-10-24', '2015-10-24 12:00:00'),
            array('12/01/2015', '2015-12-01 12:00:00'), // Us format
        );
    }

    /**
     * @dataProvider renewalDatesProvider
     */
    public function testChangeRenewalDateAction($postedDate, $actualDate)
    {
        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getId'));
        $subscription->expects($this->any())->method('getId')->willReturn(100);

        $service = $this->getModelMock('sheep_subscription/service', array('canChangeRenewalDate', 'changeRenewalDate'));
        $service->expects($this->once())->method('canChangeRenewalDate')->with($subscription)->willReturn(true);
        $service->expects($this->once())->method('changeRenewalDate')->with($subscription, $actualDate);

        $helper = $this->getHelperMock('sheep_subscription', array('getSubscriptionUrl'));
        $helper->expects($this->any())->method('getSubscriptionUrl')->willReturn('subscription view url');
        $this->replaceByMock('helper', 'sheep_subscription', $helper);

        $controller = $this->getControllerInstance('Sheep_Subscription_ServiceController', array('isManagementAllowed', 'getService', '_initSubscription', '_redirectUrl'));
        $controller->expects($this->any())->method('isManagementAllowed')->willReturn(true);
        $controller->expects($this->any())->method('getService')->willReturn($service);
        $controller->expects($this->once())->method('_initSubscription')->willReturn($subscription);
        $controller->expects($this->once())->method('_redirectUrl')->with('subscription view url');

        $this->getRequest()->setPost('renewal_date', $postedDate);
        $controller->changeRenewalDateAction();
    }
}
