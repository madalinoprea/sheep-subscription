<?php

/**
 * Class Sheep_Subscription_Test_Model_Observer
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_Model_Observer
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Model_Observer extends EcomDev_PHPUnit_Test_Case
{
    /** @var Sheep_Subscription_Model_Observer $model */
    protected $model;

    public function setUp()
    {
        parent::setUp();
        $this->model = Mage::getModel('sheep_subscription/observer');
    }


    public function testGetService()
    {
        $service = $this->model->getService();
        $this->assertNotNull($service);
        $this->assertInstanceOf('Sheep_Subscription_Model_Service', $service);
    }


    public function testAddSubscriptionOptions()
    {
        $product = $this->getModelMock('catalog/product', array('load'));

        $observer = new Varien_Event_Observer();
        $observer->setEvent(new Varien_Object(array(
            'product' => $product
        )));

        $service = $this->getModelMock('sheep_subscription/service', array('addSubscriptionOptions'));
        $service->expects($this->once())->method('addSubscriptionOptions')->with($product);
        $model = $this->getModelMock('sheep_subscription/observer', array('getService'));
        $model->expects($this->any())->method('getService')->willReturn($service);

        $model->addSubscriptionOptions($observer);
    }


    public function testAddSubscriptionOptionsOnProductCollection()
    {
        $firstProduct = Mage::getModel('catalog/product');
        $secondProduct= Mage::getModel('catalog/product');
        /** @var  $collection */
        $collection = $this->getResourceModelMock('catalog/product_collection', array('load'));
        $collection->addItem($firstProduct);
        $collection->addItem($secondProduct);

        $observer = new Varien_Event_Observer();
        $observer->setEvent(new Varien_Object(array(
            'product_collection' => $collection
        )));

        $service = $this->getModelMock('sheep_subscription/service', array('addSubscriptionOptions'));
        $service->expects($this->at(0))->method('addSubscriptionOptions')->with($firstProduct);
        $service->expects($this->at(1))->method('addSubscriptionOptions')->with($secondProduct);

        $model = $this->getModelMock('sheep_subscription/observer', array('getService'));
        $model->expects($this->any())->method('getService')->willReturn($service);

        $model->addSubscriptionOptionsOnProductCollection($observer);
    }


    /**
     * @covers Sheep_Subscription_Model_Observer::createSubscription
     */
    public function testCreateSubscription()
    {
        $quote = $this->getModelMock('sales/quote', array('getPssIsSubscription'));
        $quote->expects($this->any())->method('getPssIsSubscription')->willReturn(Sheep_Subscription_Model_Subscription::QUOTE_IS_SUBSCRIPTION_NO);
        $order = $this->getModelMock('sales/order', array('load'));

        $service = $this->getModelMock('sheep_subscription/observer', array('createSubscriptionsFromOrder'));
        $service->expects($this->once())->method('createSubscriptionsFromOrder')->with($order);

        $model = $this->getModelMock('sheep_subscription/observer', array('getService'));
        $model->expects($this->any())->method('getService')->willReturn($service);

        $observer = new Varien_Event_Observer();
        $observer->setEvent(new Varien_Object(array('quote' => $quote, 'order' => $order)));

        $model->createSubscription($observer);
    }


    /**
     * @covers Sheep_Subscription_Model_Observer::createSubscription
     */
    public function testCreateSubscriptionOnSubscriptionOrder()
    {
        $quote = $this->getModelMock('sales/quote', array('getPssIsSubscription'));
        $quote->expects($this->any())->method('getPssIsSubscription')->willReturn(Sheep_Subscription_Model_Subscription::QUOTE_IS_SUBSCRIPTION_YES);
        $order = $this->getModelMock('sales/order', array('load'));

        $service = $this->getModelMock('sheep_subscription/observer', array('createSubscriptionsFromOrder'));
        $service->expects($this->never())->method('createSubscriptionsFromOrder')->with($order);

        $model = $this->getModelMock('sheep_subscription/observer', array('getService'));
        $model->expects($this->any())->method('getService')->willReturn($service);

        $observer = new Varien_Event_Observer();
        $observer->setEvent(new Varien_Object(array('quote' => $quote, 'order' => $order)));

        $model->createSubscription($observer);
    }


    /**
     * @covers Sheep_Subscription_Model_Observer::isPaymentMethodAvailable
     */
    public function testIsPaymentMethodAvailableWithoutQuote()
    {
        $methodInstance = $this->getModelMock('paypal/express', array('getCode'));
        $methodInstance->expects($this->any())->method('getCode')->willReturn('paypal_express');

        $result = new stdClass();
        $result->isAvailable = true;

        $event = new Varien_Event(array(
            'quote'           => null,
            'method_instance' => $methodInstance,
            'result'          => $result
        ));

        $observer = new Varien_Event_Observer();
        $observer->setEvent($event);

        $this->model->isPaymentMethodAvailable($observer);

        $this->assertTrue($result->isAvailable);
    }


    /**
     * @covers Sheep_Subscription_Model_Observer::whitelistSubscriptionQuotes
     */
    public function testWhitelistSubscriptionQuotes()
    {
        $salesObserver = $this->getModelMock('sales/observer', array('setExpireQuotesAdditionalFilterFields'));
        $salesObserver->expects($this->once())->method('setExpireQuotesAdditionalFilterFields')->with(
            array('pss_is_subscription' => array('neq' => Sheep_Subscription_Model_Subscription::QUOTE_IS_SUBSCRIPTION_YES))
        );

        $observer = new Varien_Event_Observer();
        $observer->setEvent(new Varien_Object(array('sales_observer' => $salesObserver)));

        $this->model->whitelistSubscriptionQuotes($observer);
    }
}
