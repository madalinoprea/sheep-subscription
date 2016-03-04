<?php

/**
 * Class Sheep_Subscription_Test_Model_Segmentation_Service
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2016, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_Model_Segmentation_Service
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Model_Segmentation_Service extends EcomDev_PHPUnit_Test_Case
{

    public function testAssignCustomerGroup()
    {
        $groupMock = $this->getModelMock('customer/group', array('load', 'getId'));
        $groupMock->expects($this->once())->method('load')->with(3)->willReturnSelf();
        $groupMock->expects($this->any())->method('getId')->willReturn(2);
        $this->replaceByMock('model', 'customer/group', $groupMock);

        $customer = $this->getModelMock('customer/customer', array('setGroupId', 'save'));
        $customer->expects($this->once())->method('setGroupId')->with(3);
        $customer->expects($this->once())->method('save');

        $model = $this->getModelMock('sheep_subscription/segmentation_service', array('getPromotionGroupId', 'getDemotionGroupId'));
        $model->assignCustomerGroup($customer, 3);
    }


    public function testPromoteCustomer()
    {
        $customer = $this->getModelMock('customer/customer', array('load'));

        $model = $this->getModelMock('sheep_subscription/segmentation_service', array('getPromotionGroupId', 'assignCustomerGroup'));
        $model->expects($this->any())->method('getPromotionGroupId')->willReturn(3);
        $model->expects($this->atLeastOnce())->method('assignCustomerGroup')->with($customer, 3);
        $model->promoteCustomer($customer);
    }


    public function testDemoteCustomer()
    {
        $customer = $this->getModelMock('customer/customer', array('load', 'getId'));
        $customer->expects($this->any())->method('getId')->willReturn(100);

        $activeSubscriptions = $this->getResourceModelMock('sheep_subscription/subscription_collection', array('getSize'));
        $activeSubscriptions->expects($this->once())->method('getSize')->willReturn(0);

        $helper = $this->getHelperMock('sheep_subscription/subscription', array('getCustomerActiveSubscriptions'));
        $helper->expects($this->once())->method('getCustomerActiveSubscriptions')->with(100)->willReturn($activeSubscriptions);
        $this->replaceByMock('helper', 'sheep_subscription/subscription', $helper);

        $model = $this->getModelMock('sheep_subscription/segmentation_service', array('getDemotionGroupId', 'assignCustomerGroup'));
        $model->expects($this->any())->method('getDemotionGroupId')->willReturn(2);
        $model->expects($this->atLeastOnce())->method('assignCustomerGroup')->with($customer, 2);
        $model->demoteCustomer($customer);
    }


    public function testDemoteCustomerWithActiveSubscriptions()
    {
        $customer = $this->getModelMock('customer/customer', array('load', 'getId'));
        $customer->expects($this->any())->method('getId')->willReturn(100);

        $activeSubscriptions = $this->getResourceModelMock('sheep_subscription/subscription_collection', array('getSize'));
        $activeSubscriptions->expects($this->once())->method('getSize')->willReturn(1);

        $helper = $this->getHelperMock('sheep_subscription/subscription', array('getCustomerActiveSubscriptions'));
        $helper->expects($this->once())->method('getCustomerActiveSubscriptions')->with(100)->willReturn($activeSubscriptions);
        $this->replaceByMock('helper', 'sheep_subscription/subscription', $helper);

        $model = $this->getModelMock('sheep_subscription/segmentation_service', array('getDemotionGroupId', 'assignCustomerGroup'));
        $model->expects($this->any())->method('getDemotionGroupId')->willReturn(2);
        $model->expects($this->never())->method('assignCustomerGroup');
        $model->demoteCustomer($customer);
    }
}
