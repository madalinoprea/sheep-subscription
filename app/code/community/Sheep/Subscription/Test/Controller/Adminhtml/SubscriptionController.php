<?php

/**
 * Class Sheep_Subscription_Test_Adminhtml_SubscriptionController
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, {2015}, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers   Sheep_Subscription_Adminhtml_SubscriptionController
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Adminhtml_SubscriptionController extends Sheep_Util_Test_Case_Controller
{
    /** @var Sheep_Subscription_Adminhtml_SubscriptionController $controller */
    protected $controller;

    protected function setUp()
    {
        parent::setUp();
        $this->controller = $this->getControllerInstance('Sheep_Subscription_Adminhtml_SubscriptionController',
            array('getLayout', 'loadLayout', 'renderLayout', '_prepareDownloadResponse', '_redirect', '_initSubscription'));
        $this->adminSession();
    }

    protected function tearDown()
    {
        Mage::unregister('pss_subscription');
        parent::tearDown();
    }

    public function testInitSubscription()
    {
        $controller = $this->getControllerInstance('Sheep_Subscription_Adminhtml_SubscriptionController',
            array('getLayout', 'loadLayout', 'renderLayout', '_prepareDownloadResponse', '_redirect'));

        $this->getRequest()->setParam('subscription_id', 100);
        $subscriptionModel = $this->getModelMock('sheep_subscription/subscription', array('load', 'getId'));
        $subscriptionModel->expects($this->once())->method('load')->with(100)->willReturnSelf();
        $subscriptionModel->expects($this->any())->method('getId')->willReturn(100);

        $this->replaceByMock('model', 'sheep_subscription/subscription', $subscriptionModel);

        $subscription = $controller->_initSubscription();

        $this->assertNotNull($subscription);

    }

    public function testInitSubscriptionNotFound()
    {
        $controller = $this->getControllerInstance('Sheep_Subscription_Adminhtml_SubscriptionController',
            array('getLayout', 'loadLayout', 'renderLayout', '_prepareDownloadResponse', '_redirect'));
        $controller->expects($this->once())->method('_redirect')->with('*/*/');

        $this->getRequest()->setParam('subscription_id', 100);
        $subscriptionModel = $this->getModelMock('sheep_subscription/subscription', array('load', 'getId'));
        $subscriptionModel->expects($this->once())->method('load')->with(100)->willReturnSelf();
        $subscriptionModel->expects($this->any())->method('getId')->willReturn('');

        $this->replaceByMock('model', 'sheep_subscription/subscription', $subscriptionModel);

        $subscription = $controller->_initSubscription();
        $this->assertNull($subscription);
    }

    public function testInitCurrentProduct()
    {
        $this->getRequest()->setParam('product_id', 10);
        $productModel = $this->getModelMock('catalog/product', array('load', 'getId'));
        $productModel->expects($this->once())->method('load')->with(10)->willReturnSelf();
        $productModel->expects($this->any())->method('getId')->willReturn(10);
        $this->replaceByMock('model', 'catalog/product', $productModel);

        $product = $this->controller->_initCurrentProduct();
        $this->assertNull($product);
    }


    public function testIndexAction()
    {
        $this->controller->expects($this->once())->method('loadLayout');
        $this->controller->expects($this->once())->method('renderLayout');

        $this->controller->indexAction();
    }

    public function testExportCsvAction()
    {
        $this->controller->expects($this->never())->method('loadLayout');
        $this->controller->expects($this->never())->method('renderLayout');
        $this->controller->expects($this->once())->method('_prepareDownloadResponse')->with('Subscription_export.csv');

        $this->controller->exportCsvAction();
    }

    public function testViewActionWithMissingSubscription()
    {
        $this->controller->expects($this->once())->method('_initSubscription')->willReturn(null);
        $this->getRequest()->setParam('subscription_id', 10);

        $this->controller->expects($this->never())->method('loadLayout');
        $this->controller->expects($this->never())->method('renderLayout');
        $this->controller->viewAction();

        $actual = Mage::registry('pss_subscription');
        $this->assertNull($actual);
    }

    public function testViewAction()
    {
        $subscription = $this->getModelMock('sheep_subscription/subscription', array('load', 'getId'));
        $subscription->expects($this->any())->method('getId')->willReturn(10);
        $this->controller->expects($this->once())->method('_initSubscription')->willReturn($subscription);

        $this->getRequest()->setParam('subscription_id', 10);

        $this->controller->expects($this->once())->method('loadLayout');
        $this->controller->expects($this->once())->method('renderLayout');
        $this->controller->viewAction();

        $actual = Mage::registry('pss_subscription');
        $this->assertNotNull($actual);
        $this->assertEquals($subscription, $actual);
    }

    public function testPauseAction()
    {
        $subscription = $this->getModelMock('sheep_subscription/subscription', array('load', 'getId'));
        $this->controller->expects($this->once())->method('_initSubscription')->willReturn($subscription);

        $service = $this->getModelMock('sheep_subscription/service', array('pauseSubscription'));
        $service->expects($this->once())->method('pauseSubscription')->with($subscription);
        $this->replaceByMock('model', 'sheep_subscription/service', $service);

        $this->controller->pauseAction();
    }

    public function testResumeAction()
    {
        $subscription = $this->getModelMock('sheep_subscription/subscription', array('load', 'getId'));
        $this->controller->expects($this->once())->method('_initSubscription')->willReturn($subscription);

        $service = $this->getModelMock('sheep_subscription/service', array('resumeSubscription'));
        $service->expects($this->once())->method('resumeSubscription')->with($subscription);
        $this->replaceByMock('model', 'sheep_subscription/service', $service);

        $this->controller->resumeAction();
    }

    public function testCancelAction()
    {
        $subscription = $this->getModelMock('sheep_subscription/subscription', array('load', 'getId'));
        $this->controller->expects($this->once())->method('_initSubscription')->willReturn($subscription);

        $service = $this->getModelMock('sheep_subscription/service', array('cancelSubscription'));
        $service->expects($this->once())->method('cancelSubscription')->with($subscription);
        $this->replaceByMock('model', 'sheep_subscription/service', $service);

        $this->controller->cancelAction();
    }

}
