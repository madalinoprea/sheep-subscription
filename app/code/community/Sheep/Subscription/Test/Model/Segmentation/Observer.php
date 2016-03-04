<?php

/**
 * Class Sheep_Subscription_Test_Model_Segmentation_Observer
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2016, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_Model_Segmentation_Observer
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Model_Segmentation_Observer extends EcomDev_PHPUnit_Test_Case
{

    public function testGetService()
    {
        $model = Mage::getModel('sheep_subscription/segmentation_observer');

        $actual = $model->getService();
        $this->assertNotNull($actual);
        $this->assertInstanceOf('Sheep_Subscription_Model_Segmentation_Service', $actual);
    }


    public function testOnActivateSubscription()
    {
        $customer = $this->getModelMock('customer/customer');

        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getCustomerId', 'getCustomer'));
        $subscription->expects($this->any())->method('getCustomerId')->willReturn(100);
        $subscription->expects($this->any())->method('getCustomer')->willReturn($customer);

        $service = $this->getModelMock('sheep_subscription/segmentation_service', array('promoteCustomer'));
        $service->expects($this->once())->method('promoteCustomer')->with($customer);

        $model = $this->getModelMock('sheep_subscription/segmentation_observer', array('getService'));
        $model->expects($this->any())->method('getService')->willReturn($service);
        $model->onActivateSubscription(new Varien_Event_Observer(
            array('subscription'  => $subscription)
        ));
    }


    public function testOnDeactivateSubscription()
    {
        $customer = $this->getModelMock('customer/customer');

        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getCustomerId', 'getCustomer'));
        $subscription->expects($this->any())->method('getCustomerId')->willReturn(100);
        $subscription->expects($this->any())->method('getCustomer')->willReturn($customer);

        $service = $this->getModelMock('sheep_subscription/segmentation_service', array('demoteCustomer'));
        $service->expects($this->once())->method('demoteCustomer')->with($customer);

        $model = $this->getModelMock('sheep_subscription/segmentation_observer', array('getService'));
        $model->expects($this->any())->method('getService')->willReturn($service);
        $model->onDeactivateSubscription(new Varien_Event_Observer(
            array('subscription'  => $subscription)
        ));
    }

}
