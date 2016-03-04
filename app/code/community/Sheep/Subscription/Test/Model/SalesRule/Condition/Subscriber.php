<?php

/**
 * Class Sheep_Subscription_Test_Model_SalesRule_Condition_Subscriber
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2016, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_Model_SalesRule_Condition_Subscriber
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Model_SalesRule_Condition_Subscriber extends EcomDev_PHPUnit_Test_Case
{
    /** @var Sheep_Subscription_Model_SalesRule_Condition_Subscriber $model */
    protected $model;


    protected function setUp()
    {
        $this->model = Mage::getModel('sheep_subscription/salesRule_condition_subscriber');
    }


    public function testGetSubscriberTypeOptionArray()
    {
        $actual = $this->model->getSubscriberTypeOptionArray();

        $this->assertNotNull($actual);
        $this->assertCount(3, $actual);
        $this->assertEquals('Active Subscriber', $actual[0]['label']);
        $this->assertEquals('Former Subscriber', $actual[1]['label']);
        $this->assertEquals('Non Subscriber', $actual[2]['label']);
    }


    public function loadAttributeOptions()
    {
        $this->model->loadAttributeOptions();

        $actual = $this->model->getAttributeOption();
        $this->assertNotNull($actual);
        $this->assertCount(1, $actual);
        $this->assertEquals('Subscriber Type', $actual[Sheep_Subscription_Model_SalesRule_Condition_Subscriber::CONDITION_ATTRIBUTE_IS_SUBSCRIBER]);
    }


    public function testGetInputType()
    {
        $this->assertEquals('select', $this->model->getInputType());
    }


    public function testGetValueElementType()
    {
        $this->assertEquals('select', $this->model->getValueElementType());
    }


    public function testGetValueSelectOptions()
    {
        $model = $this->getModelMock('sheep_subscription/salesRule_condition_subscriber', array('getSubscriberTypeOptionArray'));
        $model->expects($this->once())->method('getSubscriberTypeOptionArray')->willReturn('options');

        $actual = $model->getValueSelectOptions();
        $this->assertEquals('options', $actual);

    }


    public function testValidateWithoutCustomer()
    {
        $helper = $this->getHelperMock('sheep_subscription/subscription', array('getCustomerSubscriptions'));
        $helper->expects($this->never())->method('getCustomerSubscriptions');
        $this->replaceByMock('helper', 'sheep_subscription/subscription', $helper);

        $model = $this->getModelMock('sheep_subscription/salesRule_condition_subscriber', array('getCustomerId', 'getValueParsed', 'getOperatorForValidate'));
        $model->expects($this->once())->method('getCustomerId')->willReturn(null);

        $actual = $model->validate(new Varien_Object());
        $this->assertFalse($actual);
    }


    public function testValidateForActiveSubscriber()
    {
        $subscriptions = $this->getResourceModelMock('sheep_subscription/subscription_collection', array('addStatusFilter', 'getSize'));
        $subscriptions->expects($this->once())->method('addStatusFilter')->with(Sheep_Subscription_Model_Subscription::STATUS_ACTIVE);
        $subscriptions->expects($this->once())->method('getSize')->willReturn(2);
        $this->replaceByMock('resource_model', 'sheep_subscription/subscription_collection', $subscriptions);

        $helper = $this->getHelperMock('sheep_subscription/subscription', array('getCustomerSubscriptions'));
        $helper->expects($this->once())->method('getCustomerSubscriptions')->with(101)->willReturn($subscriptions);
        $this->replaceByMock('helper', 'sheep_subscription/subscription', $helper);

        $model = $this->getModelMock('sheep_subscription/salesRule_condition_subscriber', array('getCustomerId', 'getValueParsed', 'getOperatorForValidate'));
        $model->expects($this->once())->method('getCustomerId')->willReturn(101);
        $model->expects($this->any())->method('getValueParsed')->willReturn(Sheep_Subscription_Model_SalesRule_Condition_Subscriber::SUBSCRIBER_TYPE_ACTIVE);
        $model->expects($this->any())->method('getOperatorForValidate')->willReturn('==');

        $actual = $model->validate(new Varien_Object());
        $this->assertTrue($actual);
    }


    public function testValidateForNonActiveSubscriber()
    {
        $subscriptions = $this->getResourceModelMock('sheep_subscription/subscription_collection', array('addStatusFilter', 'getSize'));
        $subscriptions->expects($this->once())->method('addStatusFilter')->with(Sheep_Subscription_Model_Subscription::STATUS_ACTIVE);
        $subscriptions->expects($this->once())->method('getSize')->willReturn(2);
        $this->replaceByMock('resource_model', 'sheep_subscription/subscription_collection', $subscriptions);

        $helper = $this->getHelperMock('sheep_subscription/subscription', array('getCustomerSubscriptions'));
        $helper->expects($this->once())->method('getCustomerSubscriptions')->with(101)->willReturn($subscriptions);
        $this->replaceByMock('helper', 'sheep_subscription/subscription', $helper);

        $model = $this->getModelMock('sheep_subscription/salesRule_condition_subscriber', array('getCustomerId', 'getValueParsed', 'getOperatorForValidate'));
        $model->expects($this->once())->method('getCustomerId')->willReturn(101);
        $model->expects($this->any())->method('getValueParsed')->willReturn(Sheep_Subscription_Model_SalesRule_Condition_Subscriber::SUBSCRIBER_TYPE_ACTIVE);
        $model->expects($this->any())->method('getOperatorForValidate')->willReturn('!=');

        $actual = $model->validate(new Varien_Object());
        $this->assertFalse($actual);
    }


    public function testValidateForFormerSubscriber()
    {
        $subscriptions = $this->getResourceModelMock('sheep_subscription/subscription_collection', array('addStatusFilter', 'getSize'));
        $subscriptions->expects($this->once())->method('addStatusFilter')->with(array('neq' => Sheep_Subscription_Model_Subscription::STATUS_ACTIVE));
        $subscriptions->expects($this->once())->method('getSize')->willReturn(2);
        $this->replaceByMock('resource_model', 'sheep_subscription/subscription_collection', $subscriptions);

        $helper = $this->getHelperMock('sheep_subscription/subscription', array('getCustomerSubscriptions'));
        $helper->expects($this->once())->method('getCustomerSubscriptions')->with(101)->willReturn($subscriptions);
        $this->replaceByMock('helper', 'sheep_subscription/subscription', $helper);

        $model = $this->getModelMock('sheep_subscription/salesRule_condition_subscriber', array('getCustomerId', 'getValueParsed', 'getOperatorForValidate'));
        $model->expects($this->once())->method('getCustomerId')->willReturn(101);
        $model->expects($this->any())->method('getValueParsed')->willReturn(Sheep_Subscription_Model_SalesRule_Condition_Subscriber::SUBSCRIBER_TYPE_FORMER);
        $model->expects($this->any())->method('getOperatorForValidate')->willReturn('==');

        $actual = $model->validate(new Varien_Object());
        $this->assertTrue($actual);
    }


    public function testValidateForNonFormerSubscriber()
    {
        $subscriptions = $this->getResourceModelMock('sheep_subscription/subscription_collection', array('addStatusFilter', 'getSize'));
        $subscriptions->expects($this->once())->method('addStatusFilter')->with(array('neq' => Sheep_Subscription_Model_Subscription::STATUS_ACTIVE));
        $subscriptions->expects($this->once())->method('getSize')->willReturn(2);
        $this->replaceByMock('resource_model', 'sheep_subscription/subscription_collection', $subscriptions);

        $helper = $this->getHelperMock('sheep_subscription/subscription', array('getCustomerSubscriptions'));
        $helper->expects($this->once())->method('getCustomerSubscriptions')->with(101)->willReturn($subscriptions);
        $this->replaceByMock('helper', 'sheep_subscription/subscription', $helper);

        $model = $this->getModelMock('sheep_subscription/salesRule_condition_subscriber', array('getCustomerId', 'getValueParsed', 'getOperatorForValidate'));
        $model->expects($this->once())->method('getCustomerId')->willReturn(101);
        $model->expects($this->any())->method('getValueParsed')->willReturn(Sheep_Subscription_Model_SalesRule_Condition_Subscriber::SUBSCRIBER_TYPE_FORMER);
        $model->expects($this->any())->method('getOperatorForValidate')->willReturn('!=');

        $actual = $model->validate(new Varien_Object());
        $this->assertFalse($actual);
    }


    public function testValidateForNoSubscriber()
    {
        $subscriptions = $this->getResourceModelMock('sheep_subscription/subscription_collection', array('addStatusFilter', 'getSize'));
        $subscriptions->expects($this->never())->method('addStatusFilter');
        $subscriptions->expects($this->once())->method('getSize')->willReturn(0);
        $this->replaceByMock('resource_model', 'sheep_subscription/subscription_collection', $subscriptions);

        $helper = $this->getHelperMock('sheep_subscription/subscription', array('getCustomerSubscriptions'));
        $helper->expects($this->once())->method('getCustomerSubscriptions')->with(101)->willReturn($subscriptions);
        $this->replaceByMock('helper', 'sheep_subscription/subscription', $helper);

        $model = $this->getModelMock('sheep_subscription/salesRule_condition_subscriber', array('getCustomerId', 'getValueParsed', 'getOperatorForValidate'));
        $model->expects($this->once())->method('getCustomerId')->willReturn(101);
        $model->expects($this->any())->method('getValueParsed')->willReturn(Sheep_Subscription_Model_SalesRule_Condition_Subscriber::SUBSCRIBER_TYPE_NON);
        $model->expects($this->any())->method('getOperatorForValidate')->willReturn('==');

        $actual = $model->validate(new Varien_Object());
        $this->assertTrue($actual);
    }


    public function testValidateForNonNoSubscriber()
    {
        $subscriptions = $this->getResourceModelMock('sheep_subscription/subscription_collection', array('addStatusFilter', 'getSize'));
        $subscriptions->expects($this->never())->method('addStatusFilter');
        $subscriptions->expects($this->once())->method('getSize')->willReturn(0);
        $this->replaceByMock('resource_model', 'sheep_subscription/subscription_collection', $subscriptions);

        $helper = $this->getHelperMock('sheep_subscription/subscription', array('getCustomerSubscriptions'));
        $helper->expects($this->once())->method('getCustomerSubscriptions')->with(101)->willReturn($subscriptions);
        $this->replaceByMock('helper', 'sheep_subscription/subscription', $helper);

        $model = $this->getModelMock('sheep_subscription/salesRule_condition_subscriber', array('getCustomerId', 'getValueParsed', 'getOperatorForValidate'));
        $model->expects($this->once())->method('getCustomerId')->willReturn(101);
        $model->expects($this->any())->method('getValueParsed')->willReturn(Sheep_Subscription_Model_SalesRule_Condition_Subscriber::SUBSCRIBER_TYPE_NON);
        $model->expects($this->any())->method('getOperatorForValidate')->willReturn('!=');

        $actual = $model->validate(new Varien_Object());
        $this->assertFalse($actual);
    }
}
