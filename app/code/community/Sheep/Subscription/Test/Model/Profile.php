<?php
/**
 * Class Sheep_Subscription_Test_Model_Profile
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_Model_Profile
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Model_Profile extends EcomDev_PHPUnit_Test_Case
{
    /** @var Sheep_Subscription_Model_Profile $model */
    protected $model;


    protected function setUp()
    {
        $this->model = Mage::getModel('sheep_subscription/profile');
    }


    /**
     * @covers Sheep_Subscription_Model_Resource_Profile::_construct
     */
    public function testResourceModel()
    {
        $resourceModel = $this->model->getResource();

        $this->assertInstanceOf('Sheep_Subscription_Model_Resource_Profile', $resourceModel);
        $this->assertEquals('ss_profile', $resourceModel->getMainTable());
        $this->assertEquals('customer_id', $resourceModel->getIdFieldName());
    }


    /**
     * @covers Sheep_Subscription_Model_Resource_Profile_Collection::_construct
     */
    public function testResourceCollectionModel()
    {
        $collectionModel = $this->model->getResourceCollection();
        $this->assertNotNull($collectionModel);
        $this->assertInstanceOf('Sheep_Subscription_Model_Resource_Profile_Collection', $collectionModel);
        $this->assertEquals('sheep_subscription/profile', $collectionModel->getResourceModelName());
    }


    /**
     * @covers Sheep_Subscription_Model_Profile::_construct
     */
    public function testConstruct()
    {
        $this->assertNotNull($this->model);
        $this->assertEquals('sheep_subscription/profile', $this->model->getResourceName());
    }

}
