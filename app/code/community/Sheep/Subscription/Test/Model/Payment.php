<?php
/**
 * Class Sheep_Subscription_Test_Model_Payment
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_Model_Payment
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Model_Payment extends EcomDev_PHPUnit_Test_Case
{
    /** @var Sheep_Subscription_Model_Payment $model */
    protected $model;


    protected function setUp()
    {
        $this->model = Mage::getModel('sheep_subscription/payment');
    }


    /**
     * @covers Sheep_Subscription_Model_Resource_Payment::_construct
     */
    public function testResourceModel()
    {
        $resourceModel = $this->model->getResource();

        $this->assertEquals('ss_payment', $resourceModel->getMainTable());
        $this->assertEquals('id', $resourceModel->getIdFieldName());
    }


    /**
     * @covers Sheep_Subscription_Model_Resource_Payment_Collection::_construct
     */
    public function testResourceCollectionModel()
    {
        $collectionModel = $this->model->getResourceCollection();
        $this->assertNotNull($collectionModel);
        $this->assertEquals('sheep_subscription/payment', $collectionModel->getResourceModelName());
    }


    /**
     * @covers Sheep_Subscription_Model_Payment::_construct
     */
    public function testConstruct()
    {
        $this->assertNotNull($this->model);
        $this->assertEquals('sheep_subscription/payment', $this->model->getResourceName());
    }


    /**
     * @covers Sheep_Subscription_Model_Payment::setInfo
     */
    public function testSetInfo()
    {
        $this->model->setInfo(array('last_trans_id' => 100, 'title' => 'payment'));
        $this->assertJson($this->model->getData('info'));
    }


    /**
     * @covers Sheep_Subscription_Model_Payment::getInfo
     */
    public function testGetInfo()
    {
        $this->model->setData('info', json_encode(array('trans_id' => 100, 'token' => 'aaa')));
        $actual = $this->model->getInfo();

        $this->assertNotNull($actual);
        $this->assertNotEmpty($actual);
        $this->assertEquals('aaa', $actual['token']);
    }


    /**
     * @covers Sheep_Subscription_Model_Resource_Payment_Collection::addEarlierFilter
     */
    public function testAddEarlierFilter()
    {
        $collection = $this->getResourceModelMock('sheep_subscription/payment_collection', array('addFieldToFilter'));
        $collection->expects($this->once())->method('addFieldToFilter')->with(
            'expiration_date',
            array('to' => '2016-02-29', 'datetime' => false)
        );

        $collection->addEarlierFilter('2016-02-29');
    }


    /**
     * @covers Sheep_Subscription_Model_Resource_Payment_Collection::addSubscriptionData
     */
    public function testAddSubscriptionData()
    {
        $collection = $this->getResourceModelMock('sheep_subscription/payment_collection', array('join'));
        $collection->expects($this->once())->method('join')->with($this->arrayHasKey('s'), 's.id = subscription_id', array('customer_id', 'quote_id'));

        $collection->addSubscriptionData(array('customer_id', 'quote_id'));
    }

}
