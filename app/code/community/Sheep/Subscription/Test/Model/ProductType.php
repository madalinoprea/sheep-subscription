<?php
/**
 * Class Sheep_Subscription_Test_Model_ProductType
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_Model_ProductType
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Model_ProductType extends EcomDev_PHPUnit_Test_Case
{
    /** @var Sheep_Subscription_Model_ProductType $model */
    protected $model;


    protected function setUp()
    {
        $this->model = Mage::getModel('sheep_subscription/productType');
    }


    /**
     * @covers Sheep_Subscription_Model_ProductType::_construct
     */
    public function testConstruct()
    {
        $this->assertNotNull($this->model);
        $this->assertEquals('sheep_subscription/productType', $this->model->getResourceName());
    }


    /**
     * @covers Sheep_Subscription_Model_Resource_ProductType::_construct
     */
    public function testResourceModel()
    {
        $resourceModel = $this->model->getResource();

        $this->assertEquals('ss_product_subscription_type', $resourceModel->getMainTable());
        $this->assertEquals('id', $resourceModel->getIdFieldName());
    }


    /**
     * @covers Sheep_Subscription_Model_Resource_ProductType_Collection::_construct
     */
    public function testResourceCollectionModel()
    {
        $collectionModel = $this->model->getResourceCollection();
        $this->assertNotNull($collectionModel);
        $this->assertEquals('sheep_subscription/productType', $collectionModel->getResourceModelName());
    }


    /**
     * @covers Sheep_Subscription_Model_Resource_ProductType_Collection::addProductToFilter
     */
    public function testAddProductToFilter()
    {
        $collection = $this->getResourceModelMock('sheep_subscription/productType_collection', array('addFieldToFilter'));
        $collection->expects($this->once())->method('addFieldToFilter')->with('product_id', 100);
        $collection->addProductToFilter(100);
    }


    /**
     * @covers Sheep_Subscription_Model_Resource_ProductType_Collection::addSubscriptionTypeFilter
     */
    public function testAddSubscriptionTypeToFilter()
    {
        $collection = $this->getResourceModelMock('sheep_subscription/productType_collection', array('addFieldToFilter'));
        $collection->expects($this->once())->method('addFieldToFilter')->with('type_id', 10);
        $collection->addSubscriptionTypeFilter(10);
    }

}
