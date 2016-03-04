<?php

/**
 * Class Sheep_Subscription_Test_Model_SalesRule_Condition_Subscriber
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2016, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_Model_SalesRule_Condition_Renewal
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Model_SalesRule_Condition_Renewal extends EcomDev_PHPUnit_Test_Case
{
    /** @var Sheep_Subscription_Model_SalesRule_Condition_Renewal $model */
    protected $model;


    protected function setUp()
    {
        $this->model = Mage::getModel('sheep_subscription/salesRule_condition_renewal');
    }



    public function loadAttributeOptions()
    {
        $this->model->loadAttributeOptions();

        $actual = $this->model->getAttributeOption();
        $this->assertNotNull($actual);
        $this->assertCount(1, $actual);
        $this->assertEquals('Successful Renewals', $actual[Sheep_Subscription_Model_SalesRule_Condition_Renewal::CONDITION_ATTRIBUTE_SUCCESSFUL_RENEWALS]);
    }


    public function testGetInputType()
    {
        $this->assertEquals('numeric', $this->model->getInputType());
    }


    public function testGetValueElementType()
    {
        $this->assertEquals('text', $this->model->getValueElementType());
    }


    public function testValidateWithoutCustomer()
    {
        $helper = $this->getHelperMock('sheep_subscription/renewal', array('getCustomerRenewals'));
        $helper->expects($this->never())->method('getCustomerRenewals');
        $this->replaceByMock('helper', 'sheep_subscription/renewal', $helper);

        $model = $this->getModelMock('sheep_subscription/salesRule_condition_renewal', array('getCustomerId', 'validateAttribute'));
        $model->expects($this->once())->method('getCustomerId')->willReturn(null);
        $model->expects($this->never())->method('validateAttribute');

        $actual = $model->validate(new Varien_Object());
        $this->assertFalse($actual);
    }


    public function testValidate()
    {
        $renewals = $this->getResourceModelMock('sheep_subscription/renewal_collection', array('addStatusFilter', 'getSize'));
        $renewals->expects($this->once())->method('addStatusFilter')->with(Sheep_Subscription_Model_Renewal::STATUS_PAYED);
        $renewals->expects($this->once())->method('getSize')->willReturn(2);
        $this->replaceByMock('resource_model', 'sheep_subscription/renewal_collection', $renewals);

        $helper = $this->getHelperMock('sheep_subscription/renewal', array('getCustomerRenewals'));
        $helper->expects($this->once())->method('getCustomerRenewals')->with(101)->willReturn($renewals);
        $this->replaceByMock('helper', 'sheep_subscription/renewal', $helper);

        $model = $this->getModelMock('sheep_subscription/salesRule_condition_renewal', array('getCustomerId', 'validateAttribute'));
        $model->expects($this->once())->method('getCustomerId')->willReturn(101);
        $model->expects($this->once())->method('validateAttribute')->with(2)->willReturn(true);

        $actual = $model->validate(new Varien_Object());
        $this->assertTrue($actual);
    }

}
