<?php

/**
 * Class Sheep_Subscription_Test_Model_Subscription
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, {2015}, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_Model_Subscription
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Model_Subscription extends EcomDev_PHPUnit_Test_Case
{
    /** @var Sheep_Subscription_Model_Subscription $model */
    protected $model;

    protected function setUp()
    {
        $this->model = $this->getModelMock('sheep_subscription/subscription', array('load', 'save'));
    }

    public function testConstruct()
    {
        $this->assertEquals('sheep_subscription/subscription', $this->model->getResourceName());
    }


    /**
     * @covers Sheep_Subscription_Model_Resource_Subscription::_construct
     */
    public function testResourceModel()
    {
        $resourceModel = $this->model->getResource();

        $this->assertEquals('ss_subscription', $resourceModel->getMainTable());
        $this->assertEquals('id', $resourceModel->getIdFieldName());
    }


    /**
     * @covers Sheep_Subscription_Model_Resource_Subscription_Collection::_construct
     */
    public function testResourceCollectionModel()
    {
        $collectionModel = $this->model->getResourceCollection();
        $this->assertNotNull($collectionModel);
        $this->assertEquals('sheep_subscription/subscription', $collectionModel->getResourceModelName());
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


    public function testIsActive()
    {
        $this->model->setStatus(Sheep_Subscription_Model_Subscription::STATUS_ACTIVE);
        $this->assertTrue($this->model->isActive());

        $this->model->setStatus(Sheep_Subscription_Model_Subscription::STATUS_PAUSED);
        $this->assertFalse($this->model->isActive());
    }


    public function testIsCancelled()
    {
        $this->model->setStatus(Sheep_Subscription_Model_Subscription::STATUS_CANCELLED);
        $this->assertTrue($this->model->isCancelled());

        $this->model->setStatus(Sheep_Subscription_Model_Subscription::STATUS_ACTIVE);
        $this->assertFalse($this->model->isCancelled());
    }


    public function testIsPaused()
    {
        $this->model->setStatus(Sheep_Subscription_Model_Subscription::STATUS_PAUSED);
        $this->assertTrue($this->model->isPaused());

        $this->model->setStatus(Sheep_Subscription_Model_Subscription::STATUS_CANCELLED);
        $this->assertFalse($this->model->isPaused());
    }


    public function testGetStoreDate()
    {
        return $this->markTestIncomplete('Having issues when trying to mock global locale');
        $localeMock = $this->getModelMock('core/locale', array('storeDate'));
        $localeMock->expects($this->at(0))->method('storeDate')->with(null, 1445385600, true);
        $localeMock->expects($this->at(1))->method('storeDate')->with(null, 1445472000, true);
        $this->replaceByMock('singleton', 'core/locale', $localeMock);

        $this->model->setCreatedAt('2015-10-21');
        $this->model->setStartDate('2015-10-22');

        $this->model->getCreatedAtStoreDate();
        $this->model->getStartDateStoreDate();
    }


    public function testTypeInfo()
    {
        $typeInfo = array(
            'id' => 100,
            'title' => 'subscription type'
        );

        $this->model->setTypeInfo($typeInfo);
        $typeInfo = $this->model->getTypeInfo();
        $this->assertNotNull($typeInfo);
        $this->assertArrayNotHasKey('id', $typeInfo);
        $this->assertNotNull(EcomDev_Utils_Reflection::getRestrictedPropertyValue($this->model, 'typeInfo'));

        // if typeInfo is reset we clear cached info
        $this->model->setTypeInfo($typeInfo);
        $this->assertNull(EcomDev_Utils_Reflection::getRestrictedPropertyValue($this->model, 'typeInfo'));

        $type = $this->model->getType();
        $this->assertNotNull($type);
        $this->assertInstanceOf('Sheep_Subscription_Model_Type', $type);
        $this->assertEquals('subscription type', $type->getTitle());
    }


    public function testGetShortDescription()
    {
        $quote = $this->getModelMock('sales/quote', array('getAllVisibleItems'));
        $quote->expects($this->once())->method('getAllVisibleItems')->willReturn(array(
            new Varien_Object(array('name' => 'Product A')),
            new Varien_Object(array('name' => 'Product B'))
        ));

        $model = $this->getModelMock('sheep_subscription/subscription', array('getQuote'));
        $model->expects($this->any())->method('getQuote')->willReturn($quote);

        $actual = $model->getShortDescription();
        $this->assertNotNull($actual);
        $this->assertEquals('Product A, Product B', $actual);
    }


    public function testGetQuote()
    {
        $quoteModel = $this->getModelMock('sales/quote', array('loadByIdWithoutStore'));
        $quoteModel->expects($this->once())->method('loadByIdWithoutStore')->with(200)->willReturnSelf();
        $this->replaceByMock('model', 'sales/quote', $quoteModel);

        $this->model->setQuoteId(200);
        $actual = $this->model->getQuote();
        $this->assertNotNull($actual);
        $this->assertInstanceOf('Mage_Sales_Model_Quote', $actual);
    }


    public function testGetQuoteWithoutQuoteId()
    {
        $quoteModel = $this->getModelMock('sales/quote', array('loadByIdWithoutStore'));
        $quoteModel->expects($this->never())->method('loadByIdWithoutStore');
        $this->replaceByMock('model', 'sales/quote', $quoteModel);

        $this->model->setQuoteId(null);
        $actual = $this->model->getQuote();
        $this->assertNotNull($actual);
    }


    public function testGetCustomer()
    {
        $customerModel = $this->getModelMock('customer/customer', array('load'));
        $customerModel->expects($this->once())->method('load')->with(200)->willReturnSelf();
        $this->replaceByMock('model', 'customer/customer', $customerModel);

        $this->model->setCustomerId(200);
        $actual = $this->model->getCustomer();
        $this->assertNotNull($actual);
        $this->assertInstanceOf('Mage_Customer_Model_Customer', $actual);

        // test load customer is called only once
        $actual = $this->model->getCustomer();
        $this->assertNotNull($actual);
        $this->assertInstanceOf('Mage_Customer_Model_Customer', $actual);
    }


    public function testGetStatusLabel()
    {
        $helperMock = $this->getHelperMock('sheep_subscription/subscription', array('getStatusOptions'));
        $helperMock->expects($this->atMost(3))->method('getStatusOptions')->willReturn(array(1 => 'Active', 2 => 'Paused'));
        $this->replaceByMock('helper', 'sheep_subscription/subscription', $helperMock);

        $this->model->setStatus(1);
        $this->assertEquals('Active', $this->model->getStatusLabel());

        $this->model->setStatus(2);
        $this->assertEquals('Paused', $this->model->getStatusLabel());

        $this->model->setStatus(3);
        $this->assertEquals('N/A', $this->model->getStatusLabel());
    }


    public function testGetBillingAddress()
    {
        $this->model = $this->getModelMock('sheep_subscription/subscription', array('getQuote', 'load', 'save'));
        $quote = $this->getModelMock('sales/quote', array('load', 'getBillingAddress'));
        $quote->expects($this->any())->method('getBillingAddress')->willReturn('billing address');

        $this->model->expects($this->any())->method('getQuote')->willReturn($quote);

        $actual = $this->model->getBillingAddress();
        $this->assertEquals('billing address', $actual);
    }


    public function testGetIsVirtual()
    {
        $this->model = $this->getModelMock('sheep_subscription/subscription', array('getQuote', 'load', 'save'));
        $quote = $this->getModelMock('sales/quote', array('load', 'getIsVirtual'));
        $quote->expects($this->any())->method('getIsVirtual')->willReturn(true);

        $this->model->expects($this->any())->method('getQuote')->willReturn($quote);

        $actual = $this->model->getIsVirtual();
        $this->assertTrue($actual);
    }


    public function testGetShippingAddress()
    {
        $this->model = $this->getModelMock('sheep_subscription/subscription', array('getQuote', 'load', 'save'));
        $quote = $this->getModelMock('sales/quote', array('load', 'getShippingAddress'));
        $quote->expects($this->any())->method('getShippingAddress')->willReturn('shipping address');

        $this->model->expects($this->any())->method('getQuote')->willReturn($quote);

        $actual = $this->model->getShippingAddress();
        $this->assertEquals('shipping address', $actual);
    }


    public function testGetPayment()
    {
        $this->model = $this->getModelMock('sheep_subscription/subscription', array('getQuote', 'load', 'save'));
        $quote = $this->getModelMock('sales/quote', array('load', 'getPayment'));
        $quote->expects($this->any())->method('getPayment')->willReturn('payment info');

        $this->model->expects($this->any())->method('getQuote')->willReturn($quote);

        $actual = $this->model->getPayment();
        $this->assertEquals('payment info', $actual);
    }


    public function testGetSubscriptionPayment()
    {
        $helper = $this->getHelperMock('sheep_subscription/payment', array('getSubscriptionPaymentMethodModel'));
        $helper->expects($this->once())->method('getSubscriptionPaymentMethodModel')->with('cards');
        $this->replaceByMock('helper', 'sheep_subscription/payment', $helper);

        $model = $this->getModelMock('sheep_subscription/subscription', array('getPayment'));
        $model->expects($this->once())->method('getPayment')->willReturn(
            new Varien_Object(array('method' => 'cards'))
        );

        $model->getSubscriptionPayment();
    }

    public function testGetSubscriptionPaymentInfo()
    {
        $paymentInfo = $this->getModelMock('sheep_subscription/payment', array('load', 'getInfo'));
        $paymentInfo->expects($this->once())->method('load')->with(201, 'subscription_id')->willReturnSelf();
        $paymentInfo->expects($this->once())->method('getInfo')->willReturn('payment info');
        $this->replaceByMock('model', 'sheep_subscription/payment', $paymentInfo);

        $this->model->setId(201);
        $actual = $this->model->getSubscriptionPaymentInfo();
        $this->assertEquals('payment info', $actual);
    }


    public function testGetItemsCollection()
    {
        $this->model = $this->getModelMock('sheep_subscription/subscription', array('getQuote', 'load', 'save'));
        $quote = $this->getModelMock('sales/quote', array('load', 'getItemsCollection'));
        $quote->expects($this->any())->method('getItemsCollection')->willReturn('items collection');

        $this->model->expects($this->any())->method('getQuote')->willReturn($quote);

        $actual = $this->model->getItemsCollection();
        $this->assertEquals('items collection', $actual);
    }


    public function testGetRelatedRenewals()
    {
        $renewals = $this->getResourceModelMock('sheep_subscription/renewal_collection', array('load', 'addSubscriptionFilter', 'addRenewalOrderData'));
        $renewals->expects($this->once())->method('addSubscriptionFilter')->with(100);
        $renewals->expects($this->once())->method('addRenewalOrderData')->with($this->logicalAnd($this->arrayHasKey('order_increment_id'), $this->arrayHasKey('order_status')));
        $this->replaceByMock('resource_model', 'sheep_subscription/renewal_collection', $renewals);

        $this->model->setId(100);
        $actual = $this->model->getRelatedRenewals();
        $this->assertNotNull($actual);
        $this->assertInstanceOf('Sheep_Subscription_Model_Resource_Renewal_Collection', $actual);
    }


    public function testGetNextRenewal()
    {
        $nextRenewal = $this->getModelMock('sheep_subscription/renewal', array('load'));

        $renewals = $this->getResourceModelMock('sheep_subscription/renewal_collection', array('addSubscriptionFilter', 'addStatusFilter', 'setCurPage', 'setPageSize', 'getFirstItem', 'load'));
        $renewals->expects($this->once())->method('addSubscriptionFilter')->with(401);
        $renewals->expects($this->once())->method('addStatusFilter')->with(
            array('in' => array(Sheep_Subscription_Model_Renewal::STATUS_PENDING, Sheep_Subscription_Model_Renewal::STATUS_PROCESSING, Sheep_Subscription_Model_Renewal::STATUS_WAITING))
        );
        $renewals->expects($this->once())->method('setCurPage')->with(1)->willReturnSelf();
        $renewals->expects($this->once())->method('setPageSize')->with(1)->willReturnSelf();
        $renewals->expects($this->once())->method('getFirstItem')->willReturn($nextRenewal);
        $this->replaceByMock('resource_model', 'sheep_subscription/renewal_collection', $renewals);

        $this->model->setId(401);
        $actual = $this->model->getNextRenewal();
        $this->assertNotNull($actual);
        $this->assertInstanceOf('Sheep_Subscription_Model_Renewal', $actual);

        // result is cached between consecutive calls
        $actual = $this->model->getNextRenewal();
        $this->assertEquals($nextRenewal, $actual);
    }


    /**
     * @covers Sheep_Subscription_Model_Resource_Subscription_Collection::addTypeFilter
     */
    public function testAddTypeFilter()
    {
        $collection = $this->getResourceModelMock('sheep_subscription/subscription_collection', array('addFieldToFilter'));
        $collection->expects($this->once())->method('addFieldToFilter')->with('type_id', 10);

        $collection->addTypeFilter(10);
    }


    /**
     * @covers Sheep_Subscription_Model_Resource_Subscription_Collection::addCustomerFilter
     */
    public function testAddCustomerFilter()
    {
        $collection = $this->getResourceModelMock('sheep_subscription/subscription_collection', array('addFieldToFilter'));
        $collection->expects($this->once())->method('addFieldToFilter')->with('main_table.customer_id', 10001);

        $collection->addCustomerFilter(10001);
    }


    /**
     * @covers Sheep_Subscription_Model_Resource_Subscription_Collection::addStatusFilter
     */
    public function testAddStatusFilter()
    {
        $collection = $this->getResourceModelMock('sheep_subscription/subscription_collection', array('addFieldToFilter'));
        $collection->expects($this->once())->method('addFieldToFilter')->with('main_table.status', Sheep_Subscription_Model_Subscription::STATUS_EXPIRED);

        $collection->addStatusFilter(Sheep_Subscription_Model_Subscription::STATUS_EXPIRED);
    }


    /**
     * @covers Sheep_Subscription_Model_Resource_Subscription_Collection::addOrderFilter
     */
    public function testAddOrderFilter()
    {
        $collection = $this->getResourceModelMock('sheep_subscription/subscription_collection', array('addFieldToFilter'));
        $collection->expects($this->once())->method('addFieldToFilter')->with('original_order_id', 1001);

        $collection->addOrderFilter(1001);
    }


    /**
     * @covers Sheep_Subscription_Model_Resource_Subscription_Collection::addQuoteData
     */
    public function testAddQuoteData()
    {
        $collection = $this->getResourceModelMock('sheep_subscription/subscription_collection', array('join'));
        $collection->expects($this->once())->method('join')->with(
            array('q' => 'sales/quote'),
            'q.entity_id = main_table.quote_id',
            array('field', 'second_field')
        );

        $collection->addQuoteData(array('field', 'second_field'));
    }


    /**
     * @covers Sheep_Subscription_Model_Resource_Subscription_Collection::addCustomerEmailFilter
     */
    public function testAddCustomerEmailFilter()
    {
        $select = $this->getMock('Varien_Db_Select', array('where'), array(), '', false);
        $select->expects($this->once())->method('where')->with('q.customer_email LIKE ?', '%mario%')->willReturnSelf();

        $collection = $this->getResourceModelMock('sheep_subscription/subscription_collection', array('_initSelect', 'getSelect'));
        $collection->expects($this->any())->method('getSelect')->willReturn($select);

        $collection->addCustomerEmailFilter('mario');
    }


    /**
     * @covers Sheep_Subscription_Model_Resource_Subscription_Collection::addQuoteSubtotalFilter
     */
    public function testAddQuoteSubtotalFilter()
    {
        $select = $this->getMock('Varien_Db_Select', array('where'), array(), '', false);
        $select->expects($this->at(0))->method('where')->with('q.subtotal >= ?', 10);
        $select->expects($this->at(1))->method('where')->with('q.subtotal <= ?', 39);

        $collection = $this->getResourceModelMock('sheep_subscription/subscription_collection', array('_initSelect', 'getSelect'));
        $collection->expects($this->any())->method('getSelect')->willReturn($select);

        $collection->addQuoteSubtotalFilter(10, 39);
    }


    /**
     * @covers Sheep_Subscription_Model_Resource_Subscription_Collection::addNextRenewalDate
     */
    public function testAddNextRenewalDate()
    {
        $select = $this->getMock('Varien_Db_Select', array('joinLeft'), array(), '', false);
        $select->expects($this->once())->method('joinLeft')->with(array('r' => 'ss_renewal'));

        $collection = $this->getResourceModelMock('sheep_subscription/subscription_collection', array('_initSelect', 'getSelect'));        $collection->expects($this->any())->method('getSelect')->willReturn($select);

        $collection->addNextRenewalDate();
    }

}
