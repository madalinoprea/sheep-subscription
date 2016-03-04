<?php

/**
 * Class Sheep_Subscription_Test_Helper_Type
 * @covers Sheep_Subscription_Helper_Type
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Helper_Type extends EcomDev_PHPUnit_Test_Case
{
    /** @var Sheep_Subscription_Helper_Type */
    protected $object;

    protected function setUp()
    {
        $this->object = Mage::helper('sheep_subscription/type');
    }

    public function testGetStatusOptions()
    {
        $actual = $this->object->getStatusOptions();
        $this->assertNotEmpty($actual);
        $this->assertCount(2, $actual);
        $this->assertEquals('Enabled', $actual[1]);
        $this->assertEquals('Disabled', $actual[0]);
    }

    public function testPeriodUnitOptions()
    {
        $actual = $this->object->getPeriodUnitOptions();
        $this->assertNotEmpty($actual);
        $this->assertCount(4, $actual);
        $this->assertEquals('Days', $actual['days']);
        $this->assertEquals('Weeks', $actual['weeks']);
        $this->assertEquals('Months', $actual['months']);
        $this->assertEquals('Years', $actual['years']);
    }

    public function testGetIsInfiniteOptions()
    {
        $actual = $this->object->getIsInfiniteOptions();
        $this->assertNotEmpty($actual);
        $this->assertCount(2, $actual);
        $this->assertEquals('Finite', $actual['0']);
        $this->assertEquals('Infinite', $actual['1']);
    }

    public function testGetHasTrialOptions()
    {
        $actual = $this->object->getHasTrialOptions();
        $this->assertNotEmpty($actual);
        $this->assertCount(2, $actual);
        $this->assertEquals('Without Trial', $actual[0]);
        $this->assertEquals('With Trial', $actual[1]);
    }

    public function testGetTypes()
    {
        $types = $this->object->getTypes();
        $this->assertNotNull($types);
        $this->assertInstanceOf('Sheep_Subscription_Model_Resource_Type_Collection', $types);
    }

    public function testGetAvailableTypes()
    {
        /** @var Sheep_Subscription_Helper_Type $object */
        $object = $this->getHelperMock('sheep_subscription/type', array('getTypes'));
        $collectionMock = $this->getResourceModelMock('sheep_subscription/type_collection', array('load', 'addStatusFilter'));
        $collectionMock->expects($this->once())->method('addStatusFilter')->with(Sheep_Subscription_Model_Type::STATUS_ENABLED);
        $object->expects($this->any())->method('getTypes')->will($this->returnValue($collectionMock));

        $object->getAvailableTypes();
    }

}
