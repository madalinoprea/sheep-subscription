<?php

/**
 * Class Sheep_Subscription_Test_Model_Renewal
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers   Sheep_Subscription_Model_Renewal
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Model_Renewal extends EcomDev_PHPUnit_Test_Case
{
    /** @var Sheep_Subscription_Model_Renewal $model */
    protected $model;


    protected function setUp()
    {
        $this->model = Mage::getModel('sheep_subscription/renewal');
    }


    /**
     * @covers Sheep_Subscription_Model_Renewal::_construct
     */
    public function testConstruct()
    {
        $this->assertNotNull($this->model);
        $this->assertEquals('sheep_subscription/renewal', $this->model->getResourceName());
    }


    /**
     * @covers Sheep_Subscription_Model_Resource_Renewal::_construct
     */
    public function testResourceModel()
    {
        $resourceModel = $this->model->getResource();

        $this->assertEquals('ss_renewal', $resourceModel->getMainTable());
        $this->assertEquals('id', $resourceModel->getIdFieldName());
    }


    /**
     * @covers Sheep_Subscription_Model_Resource_Renewal_Collection::_construct
     */
    public function testResourceCollectionModel()
    {
        $collectionModel = $this->model->getResourceCollection();
        $this->assertNotNull($collectionModel);
        $this->assertEquals('sheep_subscription/renewal', $collectionModel->getResourceModelName());
    }


    /**
     * Checks if updated_at and created_at are updated before save
     */
    public function testBeforeSaveForNew()
    {
        $date = $this->getModelMock('core/date', array('gmtDate'));
        $date->expects($this->any())->method('gmtDate')->willReturn('current date');
        $this->replaceByMock('singleton', 'core/date', $date);

        EcomDev_Utils_Reflection::invokeRestrictedMethod($this->model, '_beforeSave');

        $this->assertEquals('current date', $this->model->getUpdatedAt());
        $this->assertEquals('current date', $this->model->getCreatedAt());
    }


    /**
     * Checks if updated_at and created_at are updated before save
     */
    public function testBeforeSaveForExisting()
    {
        $date = $this->getModelMock('core/date', array('gmtDate'));
        $date->expects($this->any())->method('gmtDate')->willReturn('current date');
        $this->replaceByMock('singleton', 'core/date', $date);

        $this->model->setId(122);
        $this->model->setCreatedAt('creation date');
        EcomDev_Utils_Reflection::invokeRestrictedMethod($this->model, '_beforeSave');

        $this->assertEquals('current date', $this->model->getUpdatedAt());
        $this->assertEquals('creation date', $this->model->getCreatedAt());
    }


    public function testGetStatusLabel()
    {
        $helperMock = $this->getHelperMock('sheep_subscription/renewal', array('getStatusOptions'));
        $helperMock->expects($this->atMost(3))->method('getStatusOptions')->willReturn(array(
            Sheep_Subscription_Model_Renewal::STATUS_PENDING => 'Pending',
            Sheep_Subscription_Model_Renewal::STATUS_PAYED => 'Payed')
        );
        $this->replaceByMock('helper', 'sheep_subscription/renewal', $helperMock);

        $this->model->setStatus(Sheep_Subscription_Model_Renewal::STATUS_PENDING);
        $this->assertEquals('Pending', $this->model->getStatusLabel());

        $this->model->setStatus(Sheep_Subscription_Model_Renewal::STATUS_PAYED);
        $this->assertEquals('Payed', $this->model->getStatusLabel());

        $this->model->setStatus(-10);
        $this->assertEquals('N/A', $this->model->getStatusLabel());
    }


    public function testIsPayed()
    {
        $this->model->setStatus(Sheep_Subscription_Model_Renewal::STATUS_PAYED);
        $this->assertTrue($this->model->isPayed());

        $this->model->setStatus(Sheep_Subscription_Model_Renewal::STATUS_PENDING);
        $this->assertFalse($this->model->isPayed());

        $this->model->setStatus(Sheep_Subscription_Model_Renewal::STATUS_PROCESSING);
        $this->assertFalse($this->model->isPayed());

        $this->model->setStatus(Sheep_Subscription_Model_Renewal::STATUS_FAILED);
        $this->assertFalse($this->model->isPayed());

        $this->model->setStatus(Sheep_Subscription_Model_Renewal::STATUS_WAITING);
        $this->assertFalse($this->model->isPayed());
    }


    public function testIsFailed()
    {
        $this->model->setStatus(Sheep_Subscription_Model_Renewal::STATUS_FAILED);
        $this->assertTrue($this->model->isFailed());

        $this->model->setStatus(Sheep_Subscription_Model_Renewal::STATUS_PENDING);
        $this->assertFalse($this->model->isFailed());

        $this->model->setStatus(Sheep_Subscription_Model_Renewal::STATUS_PROCESSING);
        $this->assertFalse($this->model->isFailed());

        $this->model->setStatus(Sheep_Subscription_Model_Renewal::STATUS_PAYED);
        $this->assertFalse($this->model->isFailed());

        $this->model->setStatus(Sheep_Subscription_Model_Renewal::STATUS_WAITING);
        $this->assertFalse($this->model->isFailed());
    }


    /**
     * @covers Sheep_Subscription_Model_Renewal::getSubscription
     */
    public function testGetSubscription()
    {
        $subscription = $this->getModelMock('sheep_subscription/subscription', array('load'));
        $subscription->expects($this->once())->method('load')->with(100)->willReturnSelf();
        $this->replaceByMock('model', 'sheep_subscription/subscription', $subscription);

        $renewal = Mage::getModel('sheep_subscription/renewal');
        $renewal->setSubscriptionId(100);

        $actual = $renewal->getSubscription();
        $this->assertNotNull($actual);
        $this->assertEquals($subscription, $actual);

        // consecutive calls will reuse cached object
        $actual = $renewal->getSubscription();
        $this->assertNotNull($actual);
        $this->assertEquals($subscription, $actual);
    }


    /**
     * @covers Sheep_Subscription_Model_Renewal::createOrder
     */
    public function testCreateOrder()
    {
        $renewal = $this->getModelMock('sheep_subscription/renewal', array('getSubscription'));

        $service = $this->getModelMock('sales/service_quote', array('submitAll', 'getOrder'), false, array(), '', false);
        $service->expects($this->once())->method('submitAll');
        $service->expects($this->once())->method('getOrder')->willReturn(new Varien_Object());
        $this->replaceByMock('model', 'sales/service_quote', $service);

        $quote = $this->getModelMock('sales/quote', array('collectTotals', 'setPssSubscription', 'setPssRenewal'));
        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getQuote'));
        $subscription->expects($this->any())->method('getQuote')->willReturn($quote);
        $quote->expects($this->once())->method('setPssSubscription')->with($subscription);
        $quote->expects($this->once())->method('setPssRenewal')->with($renewal);

        $renewal->expects($this->any())->method('getSubscription')->willReturn($subscription);

        $renewal->createOrder();

        $this->assertEventDispatched('pss_renewal_create_order_after');
        $this->assertEventDispatched('pss_renewal_create_order_before');
    }


    /**
     * @covers                   Sheep_Subscription_Model_Renewal::createOrder
     * @expectedException Exception
     * @expectedExceptionMessage Unable to create order
     */
    public function testCreateOrderWithError()
    {
        $renewal = $this->getModelMock('sheep_subscription/renewal', array('getSubscription'));

        $service = $this->getModelMock('sales/service_quote', array('submitAll', 'getOrder'), false, array(), '', false);
        $service->expects($this->once())->method('submitAll');
        $service->expects($this->once())->method('getOrder')->willReturn(null);
        $this->replaceByMock('model', 'sales/service_quote', $service);

        $quote = $this->getModelMock('sales/quote', array('collectTotals', 'setPssSubscription', 'setPssRenewal'));
        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getQuote'));
        $subscription->expects($this->any())->method('getQuote')->willReturn($quote);
        $quote->expects($this->once())->method('setPssSubscription')->with($subscription);
        $quote->expects($this->once())->method('setPssRenewal')->with($renewal);

        $renewal->expects($this->any())->method('getSubscription')->willReturn($subscription);

        $renewal->createOrder();

        $this->assertEventDispatched('pss_renewal_create_order_after');
        $this->assertEventDispatched('pss_renewal_create_order_before');
    }


    /**
     * @covers Sheep_Subscription_Model_Resource_Renewal_Collection::addStatusFilter
     */
    public function testAddStatusFilter()
    {
        $collection = $this->getResourceModelMock('sheep_subscription/renewal_collection', array('addFieldToFilter'));
        $collection->expects($this->once())->method('addFieldToFilter')->with('main_table.status', Sheep_Subscription_Model_Renewal::STATUS_WAITING);

        $collection->addStatusFilter(Sheep_Subscription_Model_Renewal::STATUS_WAITING);
    }


    /**
     * @covers Sheep_Subscription_Model_Resource_Renewal_Collection::addEarlierFilter
     */
    public function testAddEarlierFilter()
    {
        $collection = $this->getResourceModelMock('sheep_subscription/renewal_collection', array('addFieldToFilter'));
        $collection->expects($this->once())->method('addFieldToFilter')->with(
            'date',
            array('to' => '2015-12-01', 'datetime' => true)
        );

        $collection->addEarlierFilter('2015-12-01');
    }

    /**
     * @covers Sheep_Subscription_Model_Resource_Renewal_Collection::addBetweenFilter
     */
    public function testAddBetweenFilter()
    {
        $collection = $this->getResourceModelMock('sheep_subscription/renewal_collection', array('addFieldToFilter'));
        $collection->expects($this->once())->method('addFieldToFilter')->with(
            'date',
            array('from' => '2015-12-01', 'to' => '2015-12-30', 'datetime' => true)
        );

        $collection->addBetweenFilter('2015-12-01', '2015-12-30');
    }


    /**
     * @covers Sheep_Subscription_Model_Resource_Renewal_Collection::addSubscriptionFilter
     */
    public function testAddSubscriptionFilter()
    {
        $collection = $this->getResourceModelMock('sheep_subscription/renewal_collection', array('addFieldToFilter'));
        $collection->expects($this->once())->method('addFieldToFilter')->with('main_table.subscription_id', 101);

        $collection->addSubscriptionFilter(101);
    }


    /**
     * @covers Sheep_Subscription_Model_Resource_Renewal_Collection::addCustomerFilter
     */
    public function testAddCustomerFilter()
    {
        $collection = $this->getResourceModelMock('sheep_subscription/renewal_collection', array('addSubscriptionData', 'addFieldToFilter'));
        $collection->expects($this->once())->method('addSubscriptionData')->with(array('subscription.customer_id'));
        $collection->expects($this->once())->method('addFieldToFilter')->with('subscription.customer_id', 10001);

        $collection->addCustomerFilter(10001);
    }


    /**
     * @covers Sheep_Subscription_Model_Resource_Renewal_Collection::addSubscriptionData
     */
    public function testAddSubscriptionData()
    {
        $collection = $this->getResourceModelMock('sheep_subscription/renewal_collection', array('join'));
        $collection->expects($this->once())->method('join')->with(
            array('subscription' => 'sheep_subscription/subscription'),
            'subscription.id = main_table.subscription_id',
            array('field', 'second_field')
        );

        $collection->addSubscriptionData(array('field', 'second_field'));
    }


    /**
     * @covers Sheep_Subscription_Model_Resource_Renewal_Collection::addSubscriptionQuoteData
     */
    public function testAddSubscriptionQuoteData()
    {
        $collection = $this->getResourceModelMock('sheep_subscription/renewal_collection', array('join'));
        $collection->expects($this->once())->method('join')->with(
            array('q' => 'sales/quote'),
            'q.entity_id = subscription.quote_id',
            array('field', 'second_field')
        );

        $collection->addSubscriptionQuoteData(array('field', 'second_field'));
    }


    /**
     * @covers Sheep_Subscription_Model_Resource_Renewal_Collection::addRenewalOrderData
     */
    public function testAddRenewalOrderData()
    {
        $select = $this->getMock('Varien_Db_Select', array('joinLeft'), array(), '', false);
        $select->expects($this->once())->method('joinLeft')->with(
            array('o' => 'sales_flat_order'),
            'o.entity_id = main_table.order_id',
            array('field', 'second_field')
        );

        $collection = $this->getResourceModelMock('sheep_subscription/renewal_collection', array('_initSelect', 'getSelect', 'getTable'));
        $collection->expects($this->any())->method('getSelect')->willReturn($select);
        $collection->expects($this->any())->method('getTable')->with('sales/order')->willReturn('sales_flat_order');

        $collection->addRenewalOrderData(array('field', 'second_field'));
    }
}
