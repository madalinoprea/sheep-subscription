<?php
/**
 * Class Sheep_Subscription_Test_Model_ProductTypePrice
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_Model_ProductTypePrice
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Model_ProductTypePrice extends EcomDev_PHPUnit_Test_Case
{
    /** @var Sheep_Subscription_Model_ProductTypePrice $model */
    protected $model;


    protected function setUp()
    {
        $this->model = Mage::getModel('sheep_subscription/productTypePrice');
    }


    /**
     * @covers Sheep_Subscription_Model_ProductTypePrice::_construct
     */
    public function testConstruct()
    {
        $this->assertNotNull($this->model);
        $this->assertEquals('sheep_subscription/productTypePrice', $this->model->getResourceName());
    }


    /**
     * @covers Sheep_Subscription_Model_Resource_ProductTypePrice::_construct
     */
    public function testResourceModel()
    {
        $resourceModel = $this->model->getResource();

        $this->assertEquals('ss_product_subscription_type_price', $resourceModel->getMainTable());
        $this->assertEquals('id', $resourceModel->getIdFieldName());
    }


    /**
     * @covers Sheep_Subscription_Model_Resource_ProductTypePrice_Collection::_construct
     */
    public function testResourceCollectionModel()
    {
        $collectionModel = $this->model->getResourceCollection();
        $this->assertNotNull($collectionModel);
        $this->assertEquals('sheep_subscription/productTypePrice', $collectionModel->getResourceModelName());
    }


    /**
     * @covers Sheep_Subscription_Model_Resource_ProductTypePrice_Collection::addProductToFilter
     */
    public function testAddProductToFilter()
    {
        $collection = $this->getResourceModelMock('sheep_subscription/productType_collection', array('addFieldToFilter'));
        $collection->expects($this->once())->method('addFieldToFilter')->with('product_id', 100);
        $collection->addProductToFilter(100);
    }


    /**
     * @covers Sheep_Subscription_Model_Resource_ProductTypePrice_Collection::addSubscriptionTypeFilter
     */
    public function testAddSubscriptionTypeToFilter()
    {
        $collection = $this->getResourceModelMock('sheep_subscription/productType_collection', array('addFieldToFilter'));
        $collection->expects($this->once())->method('addFieldToFilter')->with('type_id', 10);
        $collection->addSubscriptionTypeFilter(10);
    }

}
