<?php

/**
 * Class Sheep_Subscription_Test_Model_Type
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_Model_Type
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Model_Type extends EcomDev_PHPUnit_Test_Case
{
    /** @var Sheep_Subscription_Model_Type $model */
    protected $model;

    protected function setUp()
    {
        $this->model = Mage::getModel('sheep_subscription/type');
    }

    public function testConstruct()
    {
        $this->assertEquals('sheep_subscription/type', $this->model->getResourceName());
    }


    /**
     * @covers Sheep_Subscription_Model_Resource_Type::_construct
     */
    public function testResourceModel()
    {
        $resourceModel = $this->model->getResource();

        $this->assertEquals('ss_type', $resourceModel->getMainTable());
        $this->assertEquals('id', $resourceModel->getIdFieldName());
    }


    /**
     * @covers Sheep_Subscription_Model_Resource_Type_Collection::_construct
     */
    public function testResourceCollectionModel()
    {
        $collectionModel = $this->model->getResourceCollection();
        $this->assertNotNull($collectionModel);
        $this->assertEquals('sheep_subscription/type', $collectionModel->getResourceModelName());
    }

    /**
     * Checks if validate is called before save
     */
    public function testBeforeSave()
    {
        $object = $this->getModelMock('sheep_subscription/type', array('validate'));
        $object->expects($this->once())->method('validate')->with();

        EcomDev_Utils_Reflection::invokeRestrictedMethod($object, '_beforeSave');
    }

    public function testValidate()
    {
        $this->model->setIsInfinite(Sheep_Subscription_Model_Type::IS_INFINITE);
        $this->model->setHasTrial(Sheep_Subscription_Model_Type::WITHOUT_TRIAL);
        $actual = $this->model->validate();
        $this->assertTrue($actual);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Number of occurrences needs to be specified for finite subscription types.
     */
    public function testValidateWithMissingOccurrences()
    {
        $this->model->setIsInfinite(Sheep_Subscription_Model_Type::IS_FINITE);
        $this->model->setTrialOccurrences(0);
        $this->model->validate();
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Number of trial occurrences needs to be specified for subscription types with trial
     */
    public function testValidateWithMissingTrialOccurrences()
    {
        $this->model->setIsInfinite(Sheep_Subscription_Model_Type::IS_INFINITE);
        $this->model->setHasTrial(Sheep_Subscription_Model_Type::HAS_TRIAL);
        $this->model->setTrialOccurrences(0);
        $this->model->validate();
    }

    public function testGetDefaultValues()
    {
        $actual = $this->model->getDefaultValues();
        $this->assertNotEmpty($actual);
        $this->assertEquals(Sheep_Subscription_Model_Type::STATUS_ENABLED, $actual['status']);
        $this->assertEquals(1, $actual['period_count']);
        $this->assertEquals(Sheep_Subscription_Model_Type::IS_INFINITE, $actual['is_infinite']);
        $this->assertEquals(Sheep_Subscription_Model_Type::WITHOUT_TRIAL, $actual['has_trial']);
    }

    public function periodProvider()
    {
        return array(
            array(3, 'days', '2015-09-06 00:00:00'),
            array(32, 'days', '2015-10-05 00:00:00'),
            array(1, 'weeks', '2015-09-10 00:00:00'),
            array(3, 'weeks', '2015-09-24 00:00:00'),
            array(6, 'weeks', '2015-10-15 00:00:00'),
            array(1, 'months', '2015-10-03 00:00:00'),
            array(5, 'months', '2016-02-03 00:00:00'),
            array(1, 'years', '2016-09-03 00:00:00'),
            array(4, 'years', '2019-09-03 00:00:00'),
        );
    }

    /**
     * @dataProvider periodProvider
     * @param $periodCount
     * @param $periodUnit
     * @param $expected
     */
    public function testGetNextRenewalDate($periodCount, $periodUnit, $expected)
    {
        $this->model->setPeriodCount($periodCount);
        $this->model->setPeriodUnit($periodUnit);
        $actual = $this->model->getNextRenewalDate('2015-09-03');

        $this->assertEquals($expected, $actual);
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage  Unable to compute next renewal date
     */
    public function testGetNextRenewalDateWithError()
    {
        $this->model->getNextRenewalDate('assas');
    }

}
