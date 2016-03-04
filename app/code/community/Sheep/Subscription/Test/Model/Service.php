<?php

/**
 * Class Sheep_Subscription_Test_Model_Service
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, {2015}, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers   Sheep_Subscription_Model_Service
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Model_Service extends EcomDev_PHPUnit_Test_Case
{
    /** @var Sheep_Subscription_Model_Service $model */
    protected $model;

    protected function setUp()
    {
        parent::setUp();
        $this->model = Mage::getModel('sheep_subscription/service');
    }


    /**
     * Currently option id needs to be numeric and limited to 10 characters because of Product.Options.reloadPrice js object
     */
    public function testSubscriptionOptionId()
    {
        $value = Sheep_Subscription_Model_Service::SUBSCRIPTION_PRODUCT_OPTION_ID;
        $this->assertLessThanOrEqual(10, strlen($value));
        $this->assertTrue(ctype_digit($value));
    }


    public function testGetNotificationService()
    {
        $service = $this->model->getNotificationService();
        $this->assertNotNull($service);
        $this->assertInstanceOf('Sheep_Subscription_Model_Notification_Service', $service);
    }


    public function testAddProductPriceToTypeWithFixedDiscount()
    {
        // We have product
        $product = $this->getModelMock('catalog/product', array('load', 'getId'));
        $product->expects($this->any())->method('getId')->willReturn(100);

        // that has a 20 fixed discount for type 5
        $typePrice = $this->getModelMock('sheep_subscription/productTypePrice', array('getDiscount', 'getDiscountPercent'));
        $typePrice->expects($this->any())->method('getDiscount')->willReturn(20);
        $typePrice->expects($this->any())->method('getDiscountPercent')->willReturn(0);
        $typePrices = $this->getResourceModelMock('sheep_subscription/productTypePrice_collection', array('getItemByColumnValue', 'load'));
        $typePrices->expects($this->once())->method('getItemByColumnValue')->with('type_id', 5)->willReturn($typePrice);

        // so we should get price discount set to 20 (even on type by default we have 30% discount)
        $types = $this->getResourceModelMock('sheep_subscription/type_collection', array('load'));
        $type = $this->getModelMock('sheep_subscription/type', array('getId', 'getDiscount', 'setPrice', 'setPriceType'));
        $type->expects($this->any())->method('getId')->willReturn(5);
        $type->expects($this->any())->method('getDiscount')->willReturn(30);
        $type->expects($this->once())->method('setPrice')->with(-20);
        $type->expects($this->once())->method('setPriceType')->with('');
        $types->addItem($type);

        $helperMock = $this->getHelperMock('sheep_subscription/product', array('getProductSubscriptionTypePrices'));
        $helperMock->expects($this->once())->method('getProductSubscriptionTypePrices')->with($product)->willReturn($typePrices);
        $this->replaceByMock('helper', 'sheep_subscription/product', $helperMock);

        $this->model->addProductPriceToType($product, $types);
    }


    public function testAddProductPriceToTypeWithPercentDiscount()
    {
        // We have product
        $product = $this->getModelMock('catalog/product', array('load', 'getId'));
        $product->expects($this->any())->method('getId')->willReturn(100);

        // that has 10 percent discount for type 5
        $typePrice = $this->getModelMock('sheep_subscription/productTypePrice', array('getDiscount', 'getDiscountPercent'));
        $typePrice->expects($this->any())->method('getDiscount')->willReturn(0);
        $typePrice->expects($this->any())->method('getDiscountPercent')->willReturn(10);
        $typePrices = $this->getResourceModelMock('sheep_subscription/productTypePrice_collection', array('getItemByColumnValue', 'load'));
        $typePrices->expects($this->once())->method('getItemByColumnValue')->with('type_id', 5)->willReturn($typePrice);

        // so we should get price discount percent of 10% (even on type by default we have 30% discount)
        $types = $this->getResourceModelMock('sheep_subscription/type_collection', array('load'));
        $type = $this->getModelMock('sheep_subscription/type', array('getId', 'getDiscount', 'setPrice', 'setPriceType'));
        $type->expects($this->any())->method('getId')->willReturn(5);
        $type->expects($this->any())->method('getDiscount')->willReturn(30);
        $type->expects($this->once())->method('setPrice')->with(-10);
        $type->expects($this->once())->method('setPriceType')->with('percent');
        $types->addItem($type);

        $helperMock = $this->getHelperMock('sheep_subscription/product', array('getProductSubscriptionTypePrices'));
        $helperMock->expects($this->once())->method('getProductSubscriptionTypePrices')->with($product)->willReturn($typePrices);
        $this->replaceByMock('helper', 'sheep_subscription/product', $helperMock);

        $this->model->addProductPriceToType($product, $types);
    }


    public function testAddProductPriceToTypeWithDefaultDiscount()
    {
        // We have product
        $product = $this->getModelMock('catalog/product', array('load', 'getId'));
        $product->expects($this->any())->method('getId')->willReturn(100);

        // that doesn't have a discount specified for type 5
        $typePrices = $this->getResourceModelMock('sheep_subscription/productTypePrice_collection', array('getItemByColumnValue', 'load'));
        $typePrices->expects($this->once())->method('getItemByColumnValue')->with('type_id', 5)->willReturn(null);

        // so we should get price discount percent set to 30
        $types = $this->getResourceModelMock('sheep_subscription/type_collection', array('load'));
        $type = $this->getModelMock('sheep_subscription/type', array('getId', 'getDiscount', 'setPrice', 'setPriceType'));
        $type->expects($this->any())->method('getId')->willReturn(5);
        $type->expects($this->any())->method('getDiscount')->willReturn(30);
        $type->expects($this->once())->method('setPrice')->with(-30);
        $type->expects($this->once())->method('setPriceType')->with('percent');
        $types->addItem($type);

        $helperMock = $this->getHelperMock('sheep_subscription/product', array('getProductSubscriptionTypePrices'));
        $helperMock->expects($this->once())->method('getProductSubscriptionTypePrices')->with($product)->willReturn($typePrices);
        $this->replaceByMock('helper', 'sheep_subscription/product', $helperMock);

        $this->model->addProductPriceToType($product, $types);
    }


    public function testAddSubscriptionOptionsForOneTimePurchaseProduct()
    {
        $productMock = $this->getModelMock('catalog/product', array('addOption'));
        $productMock->expects($this->never())->method('addOption');

        $helperMock = $this->getHelperMock('sheep_subscription/product', array('isSubscriptionProduct'));
        $helperMock->expects($this->once())->method('isSubscriptionProduct')->with($productMock)->willReturn(false);
        $this->replaceByMock('helper', 'sheep_subscription/product', $helperMock);

        $this->model->addSubscriptionOptions($productMock);
        $this->assertNotTrue($productMock->getHasOptions());
    }


    public function testAddSubscriptionOptionsForSubscriptionProduct()
    {
        $product = $this->getModelMock('catalog/product', array('save'));
        $types = $this->getResourceModelMock('sheep_subscription/type_collection', array('load'));
        $types->addItem(new Varien_Object(array('id' => 100, 'title' => 'every day')));
        $types->addItem(new Varien_Object(array('id' => 200, 'title' => 'every week')));

        $helperMock = $this->getHelperMock('sheep_subscription/product', array('isSubscriptionProduct', 'getProductSubscriptionTypes'));
        $helperMock->expects($this->any())->method('isSubscriptionProduct')->with($product)->willReturn(true);
        $helperMock->expects($this->once())->method('getProductSubscriptionTypes')->with($product)->willReturn($types);
        $this->replaceByMock('helper', 'sheep_subscription/product', $helperMock);

        $model = $this->getModelMock('sheep_subscription/service', array('addProductPriceToType'));
        $model->expects($this->once())->method('addProductPriceToType')->with($product);
        $model->addSubscriptionOptions($product);

        $this->assertTrue($product->getHasOptions());
        $option = $product->getOptionById(Sheep_Subscription_Model_Service::SUBSCRIPTION_PRODUCT_OPTION_ID);
        $this->assertNotNull($option);
        $this->assertEquals('Subscription Type', $option->getTitle());
        $this->assertEquals('drop_down', $option->getType());
        $this->assertEquals($product, $option->getProduct());

        $values = $option->getValues();
        $this->assertNotNull($values);
        $this->assertCount(2, $values);
        $this->assertArrayHasKey('pss_subscription_type_200', $values);
        $this->assertEquals('every week', $values['pss_subscription_type_200']->getTitle());
    }


    public function testCreateSubscriptionsFromOrder()
    {
        $oneTimePurchaseMock = $this->getModelMock('catalog/product');
        $subscriptionMock = $this->getModelMock('catalog/product');

        $helperMock = $this->getHelperMock('sheep_subscription/product', array('isSubscriptionProduct'));
        $helperMock->expects($this->at(0))->method('isSubscriptionProduct')->with($oneTimePurchaseMock)->willReturn(false);
        $helperMock->expects($this->at(1))->method('isSubscriptionProduct')->with($subscriptionMock)->willReturn(true);
        $this->replaceByMock('helper', 'sheep_subscription/product', $helperMock);

        $oneTimeItem = $this->getModelMock('sales/order_item', array('getProduct'));
        $oneTimeItem->expects($this->any())->method('getProduct')->willReturn($oneTimePurchaseMock);

        $subscriptionItem = $this->getModelMock('sales/order_item', array('getId', 'getProduct'));
        $subscriptionItem->expects($this->any())->method('getId')->willReturn(100);
        $subscriptionItem->expects($this->any())->method('getProduct')->willReturn($subscriptionMock);

        $orderMock = $this->getModelMock('sales/order', array('getAllItems'));
        $orderMock->expects($this->once())->method('getAllItems')->willReturn(array($oneTimeItem, $subscriptionItem));

        $model = $this->getModelMock('sheep_subscription/service', array('getSubscriptionTypeId', 'createSubscription'));
        $model->expects($this->any())->method('getSubscriptionTypeId')->willReturn(10);
        $model->expects($this->once())->method('createSubscription')->with(10, $orderMock, array($subscriptionItem))->willReturn('subscription');

        $subscriptions = $model->createSubscriptionsFromOrder($orderMock);
        $this->assertNotEmpty($subscriptions);
        $this->assertCount(1, $subscriptions);
        $this->assertArrayHasKey(10, $subscriptions);
        $this->assertEquals('subscription', $subscriptions[10]);
    }


    /**
     * Tests that for two recurring order items with different subscription types we end up with two different subscriptions
     */
    public function testCreateSubscriptionsFromOrderWithDifferentTypes()
    {
        $firstTypeRecurringProduct = $this->getModelMock('catalog/product');
        $secondTypeRecurringProduct = $this->getModelMock('catalog/product');

        $helperMock = $this->getHelperMock('sheep_subscription/product', array('isSubscriptionProduct'));
        $helperMock->expects($this->at(0))->method('isSubscriptionProduct')->with($firstTypeRecurringProduct)->willReturn(true);
        $helperMock->expects($this->at(1))->method('isSubscriptionProduct')->with($secondTypeRecurringProduct)->willReturn(true);
        $this->replaceByMock('helper', 'sheep_subscription/product', $helperMock);

        $firstTypeOrderItem = $this->getModelMock('sales/order_item', array('getProduct'));
        $firstTypeOrderItem->expects($this->any())->method('getId')->willReturn(100);
        $firstTypeOrderItem->expects($this->any())->method('getProduct')->willReturn($firstTypeRecurringProduct);

        $secondTypeOrderItem = $this->getModelMock('sales/order_item', array('getId', 'getProduct'));
        $secondTypeOrderItem->expects($this->any())->method('getId')->willReturn(200);
        $secondTypeOrderItem->expects($this->any())->method('getProduct')->willReturn($secondTypeRecurringProduct);

        $orderMock = $this->getModelMock('sales/order', array('getAllItems'));
        $orderMock->expects($this->once())->method('getAllItems')->willReturn(array($firstTypeOrderItem, $secondTypeOrderItem));

        $model = $this->getModelMock('sheep_subscription/service', array('getSubscriptionTypeId', 'createSubscription'));
        $model->expects($this->at(0))->method('getSubscriptionTypeId')->with($firstTypeOrderItem)->willReturn(2);
        $model->expects($this->at(1))->method('getSubscriptionTypeId')->with($secondTypeOrderItem)->willReturn(4);

        $model->expects($this->at(2))->method('createSubscription')->with(2, $orderMock, array($firstTypeOrderItem))->willReturn('subscription 1');
        $model->expects($this->at(3))->method('createSubscription')->with(4, $orderMock, array($secondTypeOrderItem))->willReturn('subscription 2');

        $subscriptions = $model->createSubscriptionsFromOrder($orderMock);
        $this->assertNotEmpty($subscriptions);
        $this->assertCount(2, $subscriptions);
        $this->assertArrayHasKey(2, $subscriptions);
        $this->assertArrayHasKey(4, $subscriptions);
        $this->assertEquals('subscription 1', $subscriptions[2]);
        $this->assertEquals('subscription 2', $subscriptions[4]);
    }


    /**
     * Tests that for two recurring order items with same subscription type we end up with just one subscription
     */
    public function testCreateSubscriptionsFromOrderWithSameType()
    {
        $firstTypeRecurringProduct = $this->getModelMock('catalog/product');
        $secondTypeRecurringProduct = $this->getModelMock('catalog/product');

        $helperMock = $this->getHelperMock('sheep_subscription/product', array('isSubscriptionProduct'));
        $helperMock->expects($this->at(0))->method('isSubscriptionProduct')->with($firstTypeRecurringProduct)->willReturn(true);
        $helperMock->expects($this->at(1))->method('isSubscriptionProduct')->with($secondTypeRecurringProduct)->willReturn(true);
        $this->replaceByMock('helper', 'sheep_subscription/product', $helperMock);

        $firstTypeOrderItem = $this->getModelMock('sales/order_item', array('getProduct'));
        $firstTypeOrderItem->expects($this->any())->method('getId')->willReturn(100);
        $firstTypeOrderItem->expects($this->any())->method('getProduct')->willReturn($firstTypeRecurringProduct);

        $secondTypeOrderItem = $this->getModelMock('sales/order_item', array('getId', 'getProduct'));
        $secondTypeOrderItem->expects($this->any())->method('getId')->willReturn(200);
        $secondTypeOrderItem->expects($this->any())->method('getProduct')->willReturn($secondTypeRecurringProduct);

        $orderMock = $this->getModelMock('sales/order', array('getAllItems'));
        $orderMock->expects($this->once())->method('getAllItems')->willReturn(array($firstTypeOrderItem, $secondTypeOrderItem));

        $model = $this->getModelMock('sheep_subscription/service', array('getSubscriptionTypeId', 'createSubscription'));
        $model->expects($this->at(0))->method('getSubscriptionTypeId')->with($firstTypeOrderItem)->willReturn(2);
        $model->expects($this->at(1))->method('getSubscriptionTypeId')->with($secondTypeOrderItem)->willReturn(2);
        $model->expects($this->at(2))->method('createSubscription')->with(2, $orderMock, array($firstTypeOrderItem, $secondTypeOrderItem))->willReturn('subscription 1');

        $subscriptions = $model->createSubscriptionsFromOrder($orderMock);
        $this->assertNotEmpty($subscriptions);
        $this->assertCount(1, $subscriptions);
        $this->assertArrayHasKey(2, $subscriptions);
        $this->assertEquals('subscription 1', $subscriptions[2]);
    }


    public function testHasExistingSubscription()
    {
        $subscriptions = $this->getResourceModelMock('sheep_subscription/subscription_collection', array('addOrderFilter', 'addTypeFilter', 'getSize', 'load'));
        $subscriptions->expects($this->once())->method('addOrderFilter')->with(1001);
        $subscriptions->expects($this->once())->method('addTypeFilter')->with(5);
        $subscriptions->expects($this->once())->method('getSize')->willReturn(1);
        $this->replaceByMock('resource_model', 'sheep_subscription/subscription_collection', $subscriptions);

        $actual = $this->model->hasExistingSubscriptions(1001, 5);
        $this->assertTrue($actual);
    }


    public function testCreateSubscription()
    {
        $type = $this->getModelMock('sheep_subscription/type', array('load', 'getId'));
        $type->expects($this->once())->method('load')->with(5)->willReturnSelf();
        $type->expects($this->any())->method('getId')->willReturn(5);
        $this->replaceByMock('model', 'sheep_subscription/type', $type);

        $date = $this->getModelMock('core/date', array('gmtDate'));
        $date->expects($this->once())->method('gmtDate')->willReturn('current date');
        $this->replaceByMock('singleton', 'core/date', $date);

        $order = $this->getModelMock('sales/order', array('getId', 'getCustomerId'));
        $order->expects($this->any())->method('getId')->willReturn(1001);
        $order->expects($this->any())->method('getCustomerId')->willReturn(500);

        $orderItem = $this->getModelMock('sales/order_item', array('getOrder'));
        $orderItem->expects($this->any())->method('getOrder')->willReturn($order);

        $subscriptionPayment = $this->getModelMock('sheep_subscription/payment_local', array('onCreateSubscription'));

        // Verifies that all required data is set on subscription
        $subscriptionModel = $this->getModelMock('sheep_subscription/subscription', array(
            'setTypeId', 'setTypeInfo', 'setQuoteId', 'setCustomerId', 'setOriginalOrderId', 'setStartDate', 'setStatus',
            'getSubscriptionPayment', 'save')
        );
        $subscriptionModel->expects($this->once())->method('setTypeId')->with(5);
        $subscriptionModel->expects($this->once())->method('setTypeInfo');
        $subscriptionModel->expects($this->once())->method('setQuoteId')->with(3000);
        $subscriptionModel->expects($this->once())->method('setCustomerId')->with(500);
        $subscriptionModel->expects($this->once())->method('setOriginalOrderId')->with(1001);
        $subscriptionModel->expects($this->once())->method('setStartDate')->with('current date');
        $subscriptionModel->expects($this->once())->method('setStatus')->with(Sheep_Subscription_Model_Subscription::STATUS_ACTIVE);
        $subscriptionModel->expects($this->once())->method('save');

        // Verifies that onCreateSubscription method is called on subscription payment
        $subscriptionModel->expects($this->once())->method('save');
        $this->replaceByMock('model', 'sheep_subscription/subscription', $subscriptionModel);

        $subscriptionModel->expects($this->once())->method('getSubscriptionPayment')->willReturn($subscriptionPayment);
        $subscriptionPayment->expects($this->once())->method('onCreateSubscription')->with($subscriptionModel, $order);

        // new subscription e-mail is sent
        $notificationService = $this->getModelMock('sheep_subscription/notification_service', array('sendNewSubscriptionEmail'));
        $notificationService->expects($this->once())->method('sendNewSubscriptionEmail')->with($subscriptionModel);

        $quote = $this->getModelMock('sales/quote', array('getId'));
        $quote->expects($this->any())->method('getId')->willReturn(3000);

        $model = $this->getModelMock('sheep_subscription/service', array('hasExistingSubscriptions', '_initSubscriptionQuote', 'getNotificationService', 'getRenewal', 'sendSubscriptionEmail', 'dispatchEvent'));
        $model->expects($this->once())->method('hasExistingSubscriptions')->willReturn(false);
        $model->expects($this->any())->method('getNotificationService')->willReturn($notificationService);
        $model->expects($this->once())->method('_initSubscriptionQuote')->with($order, array($orderItem))->willReturn($quote);
        $model->expects($this->once())->method('dispatchEvent')->with('pss_create_subscription', $subscriptionModel);

        $subscription = $model->createSubscription(5, $order, array($orderItem));
        $this->assertNotNull($subscription);
        $this->assertInstanceOf('Sheep_Subscription_Model_Subscription', $subscription);
    }


    public function testInitSubscriptionQuote()
    {
        // Setup order mock
        $orderBillingAddress = $this->getModelMock('sales/order_address');
        $orderShippingAddress = $this->getModelMock('sales/order_address');
        $orderPayment = $this->getModelMock('sales/order_payment');
        $order = $this->getModelMock('sales/order', array('getBillingAddress', 'getShippingAddress', 'getShippingMethod', 'getPayment', 'getCustomerId', 'getId'));
        $order->expects($this->any())->method('getBillingAddress')->willReturn($orderBillingAddress);
        $order->expects($this->any())->method('getShippingAddress')->willReturn($orderShippingAddress);
        $order->expects($this->once())->method('getShippingMethod')->willReturn('order_shipping_method');
        $order->expects($this->once())->method('getPayment')->willReturn($orderPayment);
        $order->expects($this->any())->method('getCustomerId')->willReturn('order customer id');
        $order->expects($this->any())->method('getId')->willReturn('order id');

        $orderItem = $this->getModelMock('sales/order_item', array('load'));

        // setup quote mocks that are created by converter
        $quoteBillingAddress = $this->getModelMock('sales/quote_address');
        $quoteShippingAddress = $this->getModelMock('sales/quote_address', array('setShippingMethod', 'setCollectShippingRates'));
        $quoteShippingAddress->expects($this->once())->method('setShippingMethod')->with('order_shipping_method');
        $quoteShippingAddress->expects($this->once())->method('setCollectShippingRates')->with(1);
        $quotePayment = $this->getModelMock('sales/quote_payment');

        $subscriptionQuote = $this->getModelMock('sales/quote', array(
            'getId', 'setBillingAddress', 'setShippingAddress', 'addPayment', 'addProduct', 'setPssIsSubscription',
            'setIsActive', 'setTriggerRecollect', 'save'
        ));

        $converter = $this->getModelMock('sales/convert_order', array('toQuote', 'addressToQuoteAddress', 'toQuoteShippingAddress', 'paymentToQuotePayment'));
        $converter->expects($this->once())->method('toQuote')->with($order)->willReturn($subscriptionQuote);
        $converter->expects($this->once())->method('addressToQuoteAddress')->with($orderBillingAddress)->willReturn($quoteBillingAddress);
        $converter->expects($this->once())->method('toQuoteShippingAddress')->with($order)->willReturn($quoteShippingAddress);
        $converter->expects($this->once())->method('paymentToQuotePayment')->with($orderPayment)->willReturn($quotePayment);
        $this->replaceByMock('model', 'sales/convert_order', $converter);

        // Subscription quote expectations
        $subscriptionQuote->expects($this->any())->method('getId')->willReturn('quote id');
        $subscriptionQuote->expects($this->once())->method('setBillingAddress')->with($quoteBillingAddress);
        $subscriptionQuote->expects($this->once())->method('setShippingAddress')->with($quoteShippingAddress);
        $subscriptionQuote->expects($this->once())->method('addPayment')->with($quotePayment);
        $subscriptionQuote->expects($this->once())->method('setPssIsSubscription')->with(1);
        $subscriptionQuote->expects($this->once())->method('setIsActive')->with(0);
        $subscriptionQuote->expects($this->once())->method('setTriggerRecollect')->with(1);
        $subscriptionQuote->expects($this->once())->method('save');

        $model = $this->getModelMock('sheep_subscription/service', array('_addOrderItems'));
        $model->expects($this->once())->method('_addOrderItems')->with($subscriptionQuote, array($orderItem));

        $quote = EcomDev_Utils_Reflection::invokeRestrictedMethod($model, '_initSubscriptionQuote', array($order, array($orderItem)));
        $this->assertNotNull($quote);
        $this->assertInstanceOf('Mage_Sales_Model_Quote', $quote);
        $this->assertEquals($subscriptionQuote, $quote);
    }


    public function testAddOrderItems()
    {
        $firstProduct = Mage::getModel('catalog/product', array('id' => 100));
        $secondProduct = Mage::getModel('catalog/product', array('id' => 200));

        $productMock = $this->getModelMock('catalog/product', array('setStoreId', 'load'));
        $productMock->expects($this->atLeast(2))->method('setStoreId')->with(10)->willReturnSelf();
        $productMock->expects($this->at(1))->method('load')->with(100)->willReturn($firstProduct);
        $productMock->expects($this->at(3))->method('load')->with(200)->willReturn($secondProduct);
        $this->replaceByMock('model', 'catalog/product', $productMock);

        $firstOrderItem = $this->getModelMock('sales/order_item', array('getProductId'));
        $firstOrderItem->expects($this->any())->method('getProductId')->willReturn(100);

        $secondOrderItem = $this->getModelMock('sales/order_item', array('getProductId'));
        $secondOrderItem->expects($this->any())->method('getProductId')->willReturn(200);

        // Test saleable check flag are disabled and restored
        $catalogHelper = $this->getHelperMock('catalog/product', array('setSkipSaleableCheck'));
        $catalogHelper->expects($this->at(0))->method('setSkipSaleableCheck')->with(true);
        $catalogHelper->expects($this->at(1))->method('setSkipSaleableCheck')->with(false);
        $this->replaceByMock('helper', 'catalog/product', $catalogHelper);

        $quote = $this->getModelMock('sales/quote', array('getStoreId', 'setIsSuperMode', 'addProduct'));
        $quote->expects($this->once())->method('setIsSuperMode')->with(true);
        $quote->expects($this->any())->method('getStoreId')->willReturn(10);
        $quote->expects($this->at(2))->method('addProduct')->with($firstProduct, 'first buy request');
        $quote->expects($this->at(4))->method('addProduct')->with($secondProduct, 'second buy request');

        $model = $this->getModelMock('sheep_subscription/service', array('_getBuyRequest'));
        $model->expects($this->at(0))->method('_getBuyRequest')->with($firstOrderItem)->willReturn('first buy request');
        $model->expects($this->at(1))->method('_getBuyRequest')->with($secondOrderItem)->willReturn('second buy request');

        EcomDev_Utils_Reflection::invokeRestrictedMethod($model, '_addOrderItems', array($quote, array($firstOrderItem, $secondOrderItem)));
    }


    public function testGetBuyRequest()
    {
        $orderItem = $this->getModelMock('sales/order_item', array('getQtyOrdered', 'getProductOptions'));
        $orderItem->expects($this->once())->method('getQtyOrdered')->willReturn(10);
        $orderItem->expects($this->once())->method('getProductOptions')->willReturn(
            array(
                'options' => array(
                    array(
                        'option_id'    => 'subscription',
                        'option_value' => '100'
                    ),
                    array(
                        'option_id'    => 100,
                        'option_value' => 'custom'
                    )
                ),
                'info_buyRequest' => array(),
            )
        );

        $actual = EcomDev_Utils_Reflection::invokeRestrictedMethod($this->model, '_getBuyRequest', array($orderItem));
        $this->assertNotNull($actual);
        $this->assertInstanceOf('Varien_Object', $actual);
        $this->assertEquals(10, $actual->getQty());

        $options = $actual->getOptions();
        $this->assertNotNull($options);
        $this->assertCount(2, $options);
        $this->assertArrayHasKey('subscription', $options);
        $this->assertArrayHasKey(100, $options);
        $this->assertEquals('custom', $options[100]);
    }


    public function testGetSubscriptionTypeIdForOneTimePurchase()
    {
        $buyRequest = array(
            'options' => array(
                100 => 'some value'
            )
        );

        $orderItem = $this->getModelMock('sales/order_item', array('getProductOptionByCode'));
        $orderItem->expects($this->once())->method('getProductOptionByCode')->with('info_buyRequest')->willReturn($buyRequest);

        $typeModel = $this->getModelMock('sheep_subscription/type', array('load'));
        $typeModel->expects($this->never())->method('load');
        $this->replaceByMock('model', 'sheep_subscription/type', $typeModel);

        $actual = $this->model->getSubscriptionTypeId($orderItem);
        $this->assertNull($actual);
    }


    public function testGetSubscriptionTypeIdWithValidType()
    {
        $buyRequest = array(
            'options' => array(
                Sheep_Subscription_Model_Service::SUBSCRIPTION_PRODUCT_OPTION_ID => 'pss_subscription_type_20',
                100                                                              => 'some value'
            )
        );

        $orderItem = $this->getModelMock('sales/order_item', array('getProductOptionByCode'));
        $orderItem->expects($this->any())->method('getProductOptionByCode')->with('info_buyRequest')->willReturn($buyRequest);

        $actual = $this->model->getSubscriptionTypeId($orderItem);

        $this->assertNotNull($actual);
        $this->assertEquals(20, $actual);
    }


    public function testGetNextRenewal()
    {
        $type = $this->getModelMock('sheep_subscription/type', array('getNextRenewalDate'));
        $type->expects($this->once())->method('getNextRenewalDate')->with('2015-08-20')->willReturn('2015-12-25');

        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getId', 'getType'));
        $subscription->expects($this->once())->method('getId')->willReturn(100);
        $subscription->expects($this->once())->method('getType')->willReturn($type);

        $renewal = $this->getModelMock('sheep_subscription/renewal', array('setSubscriptionId', 'setStatus', 'setDate', 'save'));
        $renewal->expects($this->once())->method('setSubscriptionId')->with(100);
        $renewal->expects($this->once())->method('setStatus')->with(10); // Pending
        $renewal->expects($this->once())->method('setDate')->with('2015-12-25');
        $renewal->expects($this->never())->method('save');
        $this->replaceByMock('model', 'sheep_subscription/renewal', $renewal);

        $actual = $this->model->getNextRenewal($subscription, '2015-08-20');
        $this->assertNotNull($actual);
        $this->assertInstanceOf('Sheep_Subscription_Model_Renewal', $actual);
        $this->assertEquals($renewal, $actual);
    }


    public function testGetPendingRenewals()
    {
        $renewals = $this->getResourceModelMock('sheep_subscription/renewal_collection', array('addStatusFilter', 'addEarlierFilter'));
        $renewals->expects($this->once())->method('addStatusFilter')->with(10); // pending
        $renewals->expects($this->once())->method('addEarlierFilter')->with('2015-08-20');
        $this->replaceByMock('resource_model', 'sheep_subscription/renewal_collection', $renewals);

        $actual = $this->model->getPendingRenewals('2015-08-20');
        $this->assertNotNull($actual);
        $this->assertInstanceOf('Sheep_Subscription_Model_Resource_Renewal_Collection', $actual);
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Associated subscription is not active
     */
    public function testProcessRenewalWithInactiveSubscription()
    {
        $subscription = $this->getModelMock('sheep_subscription/subscription', array('isActive'));
        $subscription->expects($this->once())->method('isActive')->willReturn(false);

        $renewal = $this->getModelMock('sheep_subscription/renewal', array('getSubscription', 'createOrder', 'save'));
        $renewal->expects($this->once())->method('getSubscription')->willReturn($subscription);
        $renewal->expects($this->never())->method('createOrder');
        $renewal->expects($this->never())->method('save');

        $this->model->processRenewal($renewal);
    }


    public function testProcessRenewalWithActiveSubscription()
    {
        $subscription = $this->getModelMock('sheep_subscription/subscription', array('isActive'));
        $subscription->expects($this->once())->method('isActive')->willReturn(true);

        $order = $this->getModelMock('sales/order', array('getId', 'getCanSendNewEmailFlag', 'sendNewOrderEmail', 'save'));
        $order->expects($this->any())->method('getId')->willReturn(1003);
        $order->expects($this->any())->method('getCanSendNewEmailFlag')->willReturn(true);
        $order->expects($this->once())->method('sendNewOrderEmail');

        $renewal = $this->getModelMock('sheep_subscription/renewal', array('getDate', 'getSubscription', 'createOrder', 'setStatus', 'setOrderId', 'setLastMessage', 'save', 'isPayed'));
        $renewal->expects($this->any())->method('getDate')->willReturn('2015-08-20');
        $renewal->expects($this->once())->method('getSubscription')->willReturn($subscription);
        $renewal->expects($this->once())->method('createOrder')->willReturn($order);
        $renewal->expects($this->once())->method('setStatus')->with(30); // payed
        $renewal->expects($this->once())->method('setOrderId')->with(1003); // payed
        $renewal->expects($this->once())->method('save');
        $renewal->expects($this->any())->method('isPayed')->willReturn(true);


        $newRenewal = $this->getModelMock('sheep_subscription/renewal', array('save'));
        $newRenewal->expects($this->once())->method('save');

        $model = $this->getModelMock('sheep_subscription/service', array('getNextRenewal', 'dispatchEvent'));
        $model->expects($this->once())->method('getNextRenewal')->with($subscription, '2015-08-20')->willReturn($newRenewal);
        $model->expects($this->once())->method('dispatchEvent')->with('pss_renewal_processed', $subscription, $renewal);

        $actual = $model->processRenewal($renewal);
        $this->assertTrue($actual);
    }


    public function testProcessRenewalWithActiveSubscriptionWithErrors()
    {
        $subscription = $this->getModelMock('sheep_subscription/subscription', array('isActive'));
        $subscription->expects($this->once())->method('isActive')->willReturn(true);

        $order = $this->getModelMock('sales/order', array('getId', 'getCanSendNewEmailFlag', 'sendNewOrderEmail'));
        $order->expects($this->any())->method('getId')->willReturn(1003);
        $order->expects($this->any())->method('getCanSendNewEmailFlag')->willReturn(true);
        $order->expects($this->never())->method('sendNewOrderEmail');

        $renewal = $this->getModelMock('sheep_subscription/renewal', array('getDate', 'getSubscription', 'createOrder', 'setStatus', 'setOrderId', 'setLastMessage', 'save', 'isPayed'));
        $renewal->expects($this->any())->method('getDate')->willReturn('2015-08-20');
        $renewal->expects($this->any())->method('isPayed')->willReturn(false);
        $renewal->expects($this->once())->method('getSubscription')->willReturn($subscription);
        $renewal->expects($this->once())->method('createOrder')->will($this->throwException(new Exception('unable to create order')));
        $renewal->expects($this->never())->method('setStatus')->with(3); // payed
        $renewal->expects($this->never())->method('setOrderId')->with(1003); // payed
        $renewal->expects($this->once())->method('setLastMessage')->with('unable to create order');
        $renewal->expects($this->once())->method('save');

        $newRenewal = $this->getModelMock('sheep_subscription/renewal', array('save'));
        $newRenewal->expects($this->never())->method('save');

        $model = $this->getModelMock('sheep_subscription/service', array('getNextRenewal', 'dispatchEvent'));
        $model->expects($this->never())->method('getNextRenewal');
        $model->expects($this->once())->method('dispatchEvent')->with('pss_renewal_error', $subscription, $renewal);

        $actual = $model->processRenewal($renewal);
        $this->assertFalse($actual);
    }


    public function testDeletePendingRenewals()
    {
        $renewals = $this->getResourceModelMock('sheep_subscription/renewal_collection', array('clear', 'addStatusFilter', 'walk', 'getSize'));
        $renewals->expects($this->any())->method('getSize')->willReturn(0);
        $renewals->expects($this->once())->method('addStatusFilter')->with(10); // pending
        $renewals->expects($this->once())->method('walk')->with('delete');

        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getRelatedRenewals'));
        $subscription->expects($this->once())->method('getRelatedRenewals')->willReturn($renewals);

        EcomDev_Utils_Reflection::invokeRestrictedMethod($this->model, '_deletePendingRenewals', array($subscription));
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Unable to delete pending renewals for subscription
     */
    public function testDeletePendingRenewalsWithError()
    {
        $renewals = $this->getResourceModelMock('sheep_subscription/renewal_collection', array('clear', 'addStatusFilter', 'walk', 'getSize'));
        $renewals->expects($this->once())->method('getSize')->willReturn(12);
        $renewals->expects($this->once())->method('addStatusFilter')->with(10); // pending
        $renewals->expects($this->once())->method('walk')->with('delete');

        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getRelatedRenewals'));
        $subscription->expects($this->once())->method('getRelatedRenewals')->willReturn($renewals);

        EcomDev_Utils_Reflection::invokeRestrictedMethod($this->model, '_deletePendingRenewals', array($subscription));
    }


    public function testCanBePaused()
    {
        $subscription = $this->getModelMock('sheep_subscription/subscription', array('load', 'save'));

        $subscription->setStatus(Sheep_Subscription_Model_Subscription::STATUS_ACTIVE);
        $this->assertFalse($this->model->canBePaused($subscription));

        $subscription->setId(100);
        $subscription->setStatus(Sheep_Subscription_Model_Subscription::STATUS_ACTIVE);
        $this->assertTrue($this->model->canBePaused($subscription));

        $subscription->setId(100);
        $subscription->setStatus(Sheep_Subscription_Model_Subscription::STATUS_PAUSED);
        $this->assertFalse($this->model->canBePaused($subscription));

        $subscription->setId(100);
        $subscription->setStatus(Sheep_Subscription_Model_Subscription::STATUS_EXPIRED);
        $this->assertFalse($this->model->canBePaused($subscription));
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Subscription cannot be paused.
     */
    public function testPauseSubscriptionWithException()
    {
        $model = $this->getModelMock('sheep_subscription/service', array('canBePaused'));
        $model->expects($this->once())->method('canBePaused')->willReturn(false);

        $subscription = $this->getModelMock('sheep_subscription/subscription', array('load', 'save'));

        $model->pauseSubscription($subscription);
    }


    public function testPauseSubscription()
    {
        $connectionMock = $this->getMock('Magento_Db_Adapter_Pdo_Mysql', array('beginTransaction', 'commit', 'rollback'), array(), '', false);
        $connectionMock->expects($this->once())->method('beginTransaction');
        $connectionMock->expects($this->once())->method('commit');
        $connectionMock->expects($this->never())->method('rollback');

        $resourceMock = $this->getModelMock('core/resource', array('getConnection'));
        $resourceMock->expects($this->any())->method('getConnection')->willReturn($connectionMock);
        $this->replaceByMock('singleton', 'core/resource', $resourceMock);

        $subscription = $this->getModelMock('sheep_subscription/subscription', array('load', 'save', 'setStatus', 'getCustomerId'));
        $subscription->expects($this->once())->method('setStatus')->with(20); // Paused
        $subscription->expects($this->once())->method('save');
        $subscription->expects($this->any())->method('getCustomerId')->willReturn(100);

        $model = $this->getModelMock('sheep_subscription/service', array('canBePaused', '_deletePendingRenewals', 'dispatchEvent'));
        $model->expects($this->once())->method('canBePaused')->willReturn(true);
        $model->expects($this->once())->method('_deletePendingRenewals')->with($subscription);
        $model->expects($this->once())->method('dispatchEvent')->with('pss_pause_subscription', $subscription);

        $model->pauseSubscription($subscription);
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Unable to save
     */
    public function testPauseSubscriptionWithRollback()
    {
        $connectionMock = $this->getMock('Magento_Db_Adapter_Pdo_Mysql', array('beginTransaction', 'commit', 'rollback'), array(), '', false);
        $connectionMock->expects($this->once())->method('beginTransaction');
        $connectionMock->expects($this->never())->method('commit');
        $connectionMock->expects($this->once())->method('rollback');

        $resourceMock = $this->getModelMock('core/resource', array('getConnection'));
        $resourceMock->expects($this->any())->method('getConnection')->willReturn($connectionMock);
        $this->replaceByMock('singleton', 'core/resource', $resourceMock);

        $notificationService = $this->getModelMock('sheep_subscription/notification_service', array('notifyCustomer'));
        $notificationService->expects($this->never())->method('notifyCustomer');

        $subscription = $this->getModelMock('sheep_subscription/subscription', array('load', 'save', 'setStatus', 'getCustomerId'));
        $subscription->expects($this->once())->method('setStatus')->with(20); // Paused
        $subscription->expects($this->once())->method('save')->will($this->throwException(new Exception('Unable to save')));
        $subscription->expects($this->any())->method('getCustomerId')->willReturn(100);

        $model = $this->getModelMock('sheep_subscription/service', array('canBePaused', '_deletePendingRenewals', 'getNotificationService'));
        $model->expects($this->once())->method('canBePaused')->willReturn(true);
        $model->expects($this->once())->method('_deletePendingRenewals')->with($subscription);
        $model->expects($this->any())->method('getNotificationService')->willReturn($notificationService);

        $model->pauseSubscription($subscription);
    }


    public function testCanBeResumed()
    {
        $subscription = $this->getModelMock('sheep_subscription/subscription', array('load', 'save'));

        $subscription->setStatus(Sheep_Subscription_Model_Subscription::STATUS_PAUSED);
        $this->assertFalse($this->model->canBeResumed($subscription));

        $subscription->setId(100);
        $subscription->setStatus(Sheep_Subscription_Model_Subscription::STATUS_ACTIVE);
        $this->assertFalse($this->model->canBeResumed($subscription));

        $subscription->setId(100);
        $subscription->setStatus(Sheep_Subscription_Model_Subscription::STATUS_PAUSED);
        $this->assertTrue($this->model->canBeResumed($subscription));

        $subscription->setId(100);
        $subscription->setStatus(Sheep_Subscription_Model_Subscription::STATUS_EXPIRED);
        $this->assertFalse($this->model->canBeResumed($subscription));

    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Subscription cannot be resumed
     */
    public function testResumeSubscriptionNotAllowed()
    {
        $model = $this->getModelMock('sheep_subscription/service', array('canBeResumed'));
        $model->expects($this->once())->method('canBeResumed')->willReturn(false);

        $subscription = $this->getModelMock('sheep_subscription/subscription', array('load', 'save'));

        $model->resumeSubscription($subscription);
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Unable to create subscription schedule
     */
    public function testResumeSubscriptionWithRollback()
    {
        $connectionMock = $this->getMock('Magento_Db_Adapter_Pdo_Mysql', array('beginTransaction', 'commit', 'rollback'), array(), '', false);
        $connectionMock->expects($this->once())->method('beginTransaction');
        $connectionMock->expects($this->never())->method('commit');
        $connectionMock->expects($this->once())->method('rollback');

        $resourceMock = $this->getModelMock('core/resource', array('getConnection'));
        $resourceMock->expects($this->any())->method('getConnection')->willReturn($connectionMock);
        $this->replaceByMock('singleton', 'core/resource', $resourceMock);

        $notificationService = $this->getModelMock('sheep_subscription/notification_service', array('notifyCustomer'));
        $notificationService->expects($this->never())->method('notifyCustomer');

        $subscription = $this->getModelMock('sheep_subscription/subscription', array('load', 'save', 'setStatus', 'getCustomerId'));
        $subscription->expects($this->once())->method('setStatus')->with(10); // Active
        $subscription->expects($this->once())->method('save');
        $subscription->expects($this->any())->method('getCustomerId')->willReturn(100);

        $renewal = $this->getModelMock('sheep_subscription/renewal', array('load', 'save'));
        $renewal->expects($this->once())->method('save');

        $model = $this->getModelMock('sheep_subscription/service', array('canBeResumed', 'getNextRenewal', 'getNotificationService'));
        $model->expects($this->once())->method('canBeResumed')->with($subscription)->willReturn(true);
        $model->expects($this->once())->method('getNextRenewal')->with($subscription)->willReturn($renewal);
        $model->expects($this->any())->method('getNotificationService')->willReturn($notificationService);

        $model->resumeSubscription($subscription);
    }


    /**
     *
     */
    public function testResumeSubscriptionWithCommit()
    {
        $connectionMock = $this->getMock('Magento_Db_Adapter_Pdo_Mysql', array('beginTransaction', 'commit', 'rollback'), array(), '', false);
        $connectionMock->expects($this->once())->method('beginTransaction');
        $connectionMock->expects($this->once())->method('commit');
        $connectionMock->expects($this->never())->method('rollback');

        $resourceMock = $this->getModelMock('core/resource', array('getConnection'));
        $resourceMock->expects($this->any())->method('getConnection')->willReturn($connectionMock);
        $this->replaceByMock('singleton', 'core/resource', $resourceMock);

        $subscription = $this->getModelMock('sheep_subscription/subscription', array('load', 'save', 'setStatus', 'getCustomerId'));
        $subscription->expects($this->once())->method('setStatus')->with(10); // Active
        $subscription->expects($this->once())->method('save');
        $subscription->expects($this->any())->method('getCustomerId')->willReturn(100);

        $renewal = $this->getModelMock('sheep_subscription/renewal', array('load', 'save', 'getId'));
        $renewal->expects($this->once())->method('save');
        $renewal->expects($this->once())->method('getId')->willReturn(10);

        $model = $this->getModelMock('sheep_subscription/service', array('canBeResumed', 'getNextRenewal', 'dispatchEvent'));
        $model->expects($this->once())->method('canBeResumed')->with($subscription)->willReturn(true);
        $model->expects($this->once())->method('getNextRenewal')->with($subscription)->willReturn($renewal);
        $model->expects($this->once())->method('dispatchEvent')->with('pss_resume_subscription', $subscription);

        $model->resumeSubscription($subscription);
    }


    public function testCanBeCancelled()
    {
        $subscription = $this->getModelMock('sheep_subscription/subscription', array('load', 'save'));

        $subscription->setStatus(Sheep_Subscription_Model_Subscription::STATUS_ACTIVE);
        $this->assertFalse($this->model->canBeCancelled($subscription));

        $subscription->setId(100);
        $subscription->setStatus(Sheep_Subscription_Model_Subscription::STATUS_ACTIVE);
        $this->assertTrue($this->model->canBeCancelled($subscription));

        $subscription->setId(100);
        $subscription->setStatus(Sheep_Subscription_Model_Subscription::STATUS_PAUSED);
        $this->assertTrue($this->model->canBeCancelled($subscription));

        $subscription->setId(100);
        $subscription->setStatus(Sheep_Subscription_Model_Subscription::STATUS_EXPIRED);
        $this->assertFalse($this->model->canBeCancelled($subscription));
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Subscription cannot be cancelled
     */
    public function testCancelSubscriptionNotAllowed()
    {
        $model = $this->getModelMock('sheep_subscription/service', array('canBeCancelled'));
        $model->expects($this->once())->method('canBeCancelled')->willReturn(false);

        $subscription = $this->getModelMock('sheep_subscription/subscription', array('load', 'save'));

        $model->cancelSubscription($subscription);
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Unable to delete renewals
     */
    public function testCancelSubscriptionWithRollback()
    {
        $connectionMock = $this->getMock('Magento_Db_Adapter_Pdo_Mysql', array('beginTransaction', 'commit', 'rollback'), array(), '', false);
        $connectionMock->expects($this->once())->method('beginTransaction');
        $connectionMock->expects($this->never())->method('commit');
        $connectionMock->expects($this->once())->method('rollback');

        $resourceMock = $this->getModelMock('core/resource', array('getConnection'));
        $resourceMock->expects($this->any())->method('getConnection')->willReturn($connectionMock);
        $this->replaceByMock('singleton', 'core/resource', $resourceMock);

        $notificationService = $this->getModelMock('sheep_subscription/notification_service', array('notifyCustomer'));
        $notificationService->expects($this->never())->method('notifyCustomer');

        $subscription = $this->getModelMock('sheep_subscription/subscription', array('load', 'save', 'setStatus'));
        $subscription->expects($this->once())->method('setStatus')->with(30); // Cancelled
        $subscription->expects($this->once())->method('save');

        $model = $this->getModelMock('sheep_subscription/service', array('canBeCancelled', '_deletePendingRenewals', 'getNotificationService'));
        $model->expects($this->once())->method('canBeCancelled')->with($subscription)->willReturn(true);
        $model->expects($this->once())->method('_deletePendingRenewals')->with($subscription)
            ->will($this->throwException(new Exception('Unable to delete renewals')));
        $model->expects($this->any())->method('getNotificationService')->willReturn($notificationService);

        $model->cancelSubscription($subscription);
    }


    public function testCancelSubscriptionWithCommit()
    {
        $connectionMock = $this->getMock('Magento_Db_Adapter_Pdo_Mysql', array('beginTransaction', 'commit', 'rollback'), array(), '', false);
        $connectionMock->expects($this->once())->method('beginTransaction');
        $connectionMock->expects($this->once())->method('commit');
        $connectionMock->expects($this->never())->method('rollback');

        $resourceMock = $this->getModelMock('core/resource', array('getConnection'));
        $resourceMock->expects($this->any())->method('getConnection')->willReturn($connectionMock);
        $this->replaceByMock('singleton', 'core/resource', $resourceMock);

        $subscription = $this->getModelMock('sheep_subscription/subscription', array('load', 'save', 'setStatus', 'getCustomerId', 'setCanceledAt'));
        $subscription->expects($this->once())->method('setStatus')->with(30); // Canceled
        $subscription->expects($this->once())->method('setCanceledAt');
        $subscription->expects($this->once())->method('save');
        $subscription->expects($this->any())->method('getCustomerId')->willReturn(100);

        $model = $this->getModelMock('sheep_subscription/service', array('canBeCancelled', '_deletePendingRenewals', 'dispatchEvent'));
        $model->expects($this->once())->method('canBeCancelled')->with($subscription)->willReturn(true);
        $model->expects($this->once())->method('_deletePendingRenewals')->with($subscription);
        $model->expects($this->once())->method('dispatchEvent')->with('pss_cancel_subscription', $subscription);

        $model->cancelSubscription($subscription);
    }


    /**
     * Tests the subscription status is set to expired
     */
    public function testExpireSubscription()
    {
        $subscription = $this->getModelMock('sheep_subscription/subscription', array('setStatus', 'save', 'getCustomerId'));
        $subscription->expects($this->once())->method('setStatus')->with(Sheep_Subscription_Model_Subscription::STATUS_EXPIRED)->willReturnSelf();
        $subscription->expects($this->once())->method('save');
        $subscription->expects($this->any())->method('getCustomerId')->willReturn(100);

        $model = $this->getModelMock('sheep_subscription/service', array('dispatchEvent'));
        $model->expects($this->once())->method('dispatchEvent')->with('pss_expire_subscription', $subscription);

        $model->expireSubscription($subscription);
    }


    /**
     * @covers Sheep_Subscription_Model_Service::canChangeRenewalDate
     */
    public function testCanChangeRenewalDate()
    {
        $helperMock = $this->getHelperMock('sheep_subscription', array('getIsAccountRenewalEditAllowed'));
        $helperMock->expects($this->atLeastOnce())->method('getIsAccountRenewalEditAllowed')->willReturn(true);
        $this->replaceByMock('helper', 'sheep_subscription', $helperMock);

        $subscription = $this->getModelMock('sheep_subscription/subscription', array('load', 'save'));

        $subscription->setStatus(Sheep_Subscription_Model_Subscription::STATUS_ACTIVE);
        $this->assertFalse($this->model->canChangeRenewalDate($subscription));

        $subscription->setId(100);
        $subscription->setStatus(Sheep_Subscription_Model_Subscription::STATUS_ACTIVE);
        $this->assertTrue($this->model->canChangeRenewalDate($subscription));

        $subscription->setId(100);
        $subscription->setStatus(Sheep_Subscription_Model_Subscription::STATUS_PAUSED);
        $this->assertFalse($this->model->canChangeRenewalDate($subscription));

        $subscription->setId(100);
        $subscription->setStatus(Sheep_Subscription_Model_Subscription::STATUS_EXPIRED);
        $this->assertFalse($this->model->canChangeRenewalDate($subscription));
    }


    /**
     * @covers Sheep_Subscription_Model_Service::changeRenewalDate
     */
    public function testChangeRenewalDate()
    {
        $date = '2018-04-01 12:00:00';

        $renewal = $this->getModelMock('sheep_subscription/renewal', array('getId', 'getStatus', 'setDate', 'save'));
        $renewal->expects($this->any())->method('getId')->willReturn(100);
        $renewal->expects($this->any())->method('getStatus')->willReturn(Sheep_Subscription_Model_Renewal::STATUS_PENDING);
        $renewal->expects($this->once())->method('setDate')->with($date);
        $renewal->expects($this->once())->method('save');

        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getNextRenewal', 'getCustomerId'));
        $subscription->expects($this->once())->method('getNextRenewal')->willReturn($renewal);
        $subscription->expects($this->any())->method('getCustomerId')->willReturn(100);

        $model = $this->getModelMock('sheep_subscription/service', array('canChangeRenewalDate', 'dispatchEvent'));
        $model->expects($this->once())->method('canChangeRenewalDate')->with($subscription)->willReturn(true);
        $model->expects($this->once())->method('dispatchEvent')->with('pss_change_renewal_date', $subscription);

        $model->changeRenewalDate($subscription, $date);
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Renewal date cannot be changed
     * @covers Sheep_Subscription_Model_Service::changeRenewalDate
     */
    public function testChangeRenewalDateNotPermitted()
    {
        $date = '2018-04-01 12:00:00';

        $renewal = $this->getModelMock('sheep_subscription/renewal', array('getId', 'getStatus', 'setDate', 'save'));
        $renewal->expects($this->any())->method('getId')->willReturn(100);
        $renewal->expects($this->never())->method('save');

        $notificationService = $this->getModelMock('sheep_subscription/notification_service', array('notifyCustomer'));
        $notificationService->expects($this->never())->method('notifyCustomer');

        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getNextRenewal'));
        $subscription->expects($this->never())->method('getNextRenewal');

        $model = $this->getModelMock('sheep_subscription/service', array('canChangeRenewalDate', 'getNotificationService'));
        $model->expects($this->once())->method('canChangeRenewalDate')->with($subscription)->willReturn(false);
        $model->expects($this->any())->method('getNotificationService')->willReturn($notificationService);

        $model->changeRenewalDate($subscription, $date);
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Next renewal cannot be found
     * @covers Sheep_Subscription_Model_Service::changeRenewalDate
     */
    public function testChangeRenewalDateWithoutRenewal()
    {
        $date = '2018-04-01 12:00:00';

        $renewal = $this->getModelMock('sheep_subscription/renewal', array('getId', 'getStatus', 'setDate', 'save'));
        $renewal->expects($this->any())->method('getId')->willReturn(null);
        $renewal->expects($this->any())->method('getStatus')->willReturn(Sheep_Subscription_Model_Renewal::STATUS_PENDING);
        $renewal->expects($this->never())->method('setDate')->with($date);
        $renewal->expects($this->never())->method('save');

        $notificationService = $this->getModelMock('sheep_subscription/notification_service', array('notifyCustomer'));
        $notificationService->expects($this->never())->method('notifyCustomer');

        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getNextRenewal'));
        $subscription->expects($this->once())->method('getNextRenewal')->willReturn($renewal);

        $model = $this->getModelMock('sheep_subscription/service', array('canChangeRenewalDate', 'getNotificationService'));
        $model->expects($this->once())->method('canChangeRenewalDate')->with($subscription)->willReturn(true);
        $model->expects($this->any())->method('getNotificationService')->willReturn($notificationService);

        $model->changeRenewalDate($subscription, $date);
    }


    public function invalidRenewalStatusProvider()
    {
        return array(
            array(Sheep_Subscription_Model_Renewal::STATUS_FAILED),
            array(Sheep_Subscription_Model_Renewal::STATUS_PAYED),
            array(Sheep_Subscription_Model_Renewal::STATUS_PROCESSING),
            array(Sheep_Subscription_Model_Renewal::STATUS_WAITING),
        );
    }


    /**
     * @dataProvider invalidRenewalStatusProvider
     * @expectedException Exception
     * @expectedExceptionMessage Renewal cannot be changed in current state
     * @covers Sheep_Subscription_Model_Service::changeRenewalDate
     * @param $renewalStatus
     */
    public function testChangeRenewalDateWithIncorrectStatus($renewalStatus)
    {
        $date = '2018-04-01 12:00:00';

        $renewal = $this->getModelMock('sheep_subscription/renewal', array('getId', 'getStatus', 'setDate', 'save'));
        $renewal->expects($this->any())->method('getId')->willReturn(100);
        $renewal->expects($this->any())->method('getStatus')->willReturn($renewalStatus);
        $renewal->expects($this->never())->method('save');

        $notificationService = $this->getModelMock('sheep_subscription/notification_service', array('notifyCustomer'));
        $notificationService->expects($this->never())->method('notifyCustomer');

        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getNextRenewal'));
        $subscription->expects($this->once())->method('getNextRenewal')->willReturn($renewal);

        $model = $this->getModelMock('sheep_subscription/service', array('canChangeRenewalDate', 'getNotificationService'));
        $model->expects($this->once())->method('canChangeRenewalDate')->with($subscription)->willReturn(true);
        $model->expects($this->any())->method('getNotificationService')->willReturn($notificationService);

        $model->changeRenewalDate($subscription, $date);
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Please specify a valid date format
     * @covers Sheep_Subscription_Model_Service::changeRenewalDate
     */
    public function testChangeRenewalDateWithInvalidDate()
    {
        $date = 'assa';

        $renewal = $this->getModelMock('sheep_subscription/renewal', array('getId', 'getStatus', 'setDate', 'save'));
        $renewal->expects($this->any())->method('getId')->willReturn(100);
        $renewal->expects($this->any())->method('getStatus')->willReturn(Sheep_Subscription_Model_Renewal::STATUS_PENDING);
        $renewal->expects($this->never())->method('setDate');
        $renewal->expects($this->never())->method('save');

        $notificationService = $this->getModelMock('sheep_subscription/notification_service', array('notifyCustomer'));
        $notificationService->expects($this->never())->method('notifyCustomer');

        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getNextRenewal'));
        $subscription->expects($this->once())->method('getNextRenewal')->willReturn($renewal);

        $model = $this->getModelMock('sheep_subscription/service', array('canChangeRenewalDate', 'getNotificationService'));
        $model->expects($this->once())->method('canChangeRenewalDate')->with($subscription)->willReturn(true);
        $model->expects($this->any())->method('getNotificationService')->willReturn($notificationService);

        $model->changeRenewalDate($subscription, $date);
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Please specify a date in future
     * @covers Sheep_Subscription_Model_Service::changeRenewalDate
     */
    public function testChangeRenewalDateWithDateInPast()
    {
        $date = '2014-04-05 12:00:00';

        $renewal = $this->getModelMock('sheep_subscription/renewal', array('getId', 'getStatus', 'setDate', 'save'));
        $renewal->expects($this->any())->method('getId')->willReturn(100);
        $renewal->expects($this->any())->method('getStatus')->willReturn(Sheep_Subscription_Model_Renewal::STATUS_PENDING);
        $renewal->expects($this->never())->method('setDate');
        $renewal->expects($this->never())->method('save');

        $notificationService = $this->getModelMock('sheep_subscription/notification_service', array('notifyCustomer'));
        $notificationService->expects($this->never())->method('notifyCustomer');

        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getNextRenewal'));
        $subscription->expects($this->once())->method('getNextRenewal')->willReturn($renewal);

        $model = $this->getModelMock('sheep_subscription/service', array('canChangeRenewalDate', 'getNotificationService'));
        $model->expects($this->once())->method('canChangeRenewalDate')->with($subscription)->willReturn(true);
        $model->expects($this->any())->method('getNotificationService')->willReturn($notificationService);

        $model->changeRenewalDate($subscription, $date);
    }


    public function testCanChangeShippingInformation()
    {
        $helperMock = $this->getHelperMock('sheep_subscription', array('getIsAccountShippingEditAllowed'));
        $helperMock->expects($this->atLeastOnce())->method('getIsAccountShippingEditAllowed')->willReturn(true);
        $this->replaceByMock('helper', 'sheep_subscription', $helperMock);

        $quote =  $this->getModelMock('sales/quote', array('isVirtual'));
        $quote->expects($this->atLeastOnce())->method('isVirtual')->willReturn(false);

        $subscription = $this->getModelMock('sheep_subscription/subscription', array('load', 'save', 'getQuote'));
        $subscription->expects($this->any())->method('getQuote')->willReturn($quote);

        // subscription without id
        $subscription->setStatus(Sheep_Subscription_Model_Subscription::STATUS_ACTIVE);
        $this->assertFalse($this->model->canChangeShippingInformation($subscription));

        $subscription->setId(100);
        $subscription->setStatus(Sheep_Subscription_Model_Subscription::STATUS_ACTIVE);
        $this->assertTrue($this->model->canChangeShippingInformation($subscription));

        $subscription->setId(100);
        $subscription->setStatus(Sheep_Subscription_Model_Subscription::STATUS_PAUSED);
        $this->assertTrue($this->model->canChangeShippingInformation($subscription));

        $subscription->setId(100);
        $subscription->setStatus(Sheep_Subscription_Model_Subscription::STATUS_EXPIRED);
        $this->assertFalse($this->model->canChangeShippingInformation($subscription));
    }


    public function testSetSubscriptionShippingAddress()
    {
        $address = $this->getModelMock('customer/address', array('load', 'getId', 'getCustomerId'));
        $address->expects($this->once())->method('load')->with(501)->willReturnSelf();
        $address->expects($this->any())->method('getId')->willReturn(501);
        $address->expects($this->any())->method('getCustomerId')->willReturn(10);

        $this->replaceByMock('model', 'customer/address', $address);

        $quoteAddress = $this->getModelMock('sales/quote', array(
            'importCustomerAddress', 'setSaveInAddressBook', 'getData', 'implodeStreetAddress', 'setCollectShippingRates', 'validate')
        );
        $quoteAddress->expects($this->once())->method('importCustomerAddress')->with($address)->willReturnSelf();
        $quoteAddress->expects($this->any())->method('getData')->willReturn(array('address data'));
        $quoteAddress->expects($this->once())->method('implodeStreetAddress');
        $quoteAddress->expects($this->once())->method('setCollectShippingRates')->with(1);
        $quoteAddress->expects($this->once())->method('validate')->willReturn(true);
        $quoteAddress->expects($this->once())->method('setSaveInAddressBook')->with(0);

        $quote = $this->getModelMock('sales/quote', array('getShippingAddress', 'collectTotals', 'save'));
        $quote->expects($this->any())->method('getShippingAddress')->willReturn($quoteAddress);
        $quote->expects($this->once())->method('collectTotals')->willReturnSelf();
        $quote->expects($this->once())->method('save');

        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getCustomerId', 'getQuote'));
        $subscription->expects($this->any())->method('getCustomerId')->willReturn(10);
        $subscription->expects($this->any())->method('getQuote')->willReturn($quote);

        $addressForm = $this->getModelMock('customer/form', array('setFormCode', 'setEntityType', 'setEntity', 'validateData'));
        $addressForm->expects($this->once())->method('setFormCode')->with('customer_address_edit')->willReturnSelf();
        $addressForm->expects($this->once())->method('setEntityType')->with('customer_address')->willReturnSelf();
        $addressForm->expects($this->once())->method('setEntity')->with($quoteAddress);
        $addressForm->expects($this->once())->method('validateData')->with(array('address data'))->willReturn(true);
        $this->replaceByMock('model', 'customer/form', $addressForm);

        $model = $this->getModelMock('sheep_subscription/service', array('canChangeShippingInformation'));
        $model->expects($this->once())->method('canChangeShippingInformation')->with($subscription)->willReturn(true);

        $actual = $model->setSubscriptionShippingAddress($subscription, 501);
        $this->assertNotNull($actual);
        $this->assertCount(0, $actual);
    }


    public function testSetSubscriptionShippingAddressNotAllowed()
    {
        $quote = $this->getModelMock('sales/quote', array('getShippingAddress', 'collectTotals', 'save'));
        $quote->expects($this->never())->method('save');

        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getCustomerId', 'getQuote'));
        $subscription->expects($this->any())->method('getQuote')->willReturn($quote);

        $model = $this->getModelMock('sheep_subscription/service', array('canChangeShippingInformation'));
        $model->expects($this->once())->method('canChangeShippingInformation')->with($subscription)->willReturn(false);

        $actual = $model->setSubscriptionShippingAddress($subscription, 501);
        $this->assertNotNull($actual);
        $this->assertCount(1, $actual);
        $this->assertEquals('Shipping address cannot be changed on this subscription.', $actual[0]);
    }


    public function testSetSubscriptionShippingAddressAddressNotFound()
    {
        $address = $this->getModelMock('customer/address', array('load', 'getId', 'getCustomerId'));
        $address->expects($this->once())->method('load')->with(501)->willReturnSelf();
        $address->expects($this->any())->method('getId')->willReturn(null);

        $this->replaceByMock('model', 'customer/address', $address);

        $quote = $this->getModelMock('sales/quote', array('getShippingAddress', 'collectTotals', 'save'));
        $quote->expects($this->never())->method('save');

        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getCustomerId', 'getQuote'));
        $subscription->expects($this->any())->method('getCustomerId')->willReturn(10);
        $subscription->expects($this->any())->method('getQuote')->willReturn($quote);

        $model = $this->getModelMock('sheep_subscription/service', array('canChangeShippingInformation'));
        $model->expects($this->once())->method('canChangeShippingInformation')->with($subscription)->willReturn(true);

        $actual = $model->setSubscriptionShippingAddress($subscription, 501);
        $this->assertNotNull($actual);
        $this->assertCount(1, $actual);
        $this->assertEquals('Specified customer address was not found.', $actual[0]);
    }


    public function testSetSubscriptionShippingAddressInvalidAddress()
    {
        $address = $this->getModelMock('customer/address', array('load', 'getId', 'getCustomerId'));
        $address->expects($this->once())->method('load')->with(501)->willReturnSelf();
        $address->expects($this->any())->method('getId')->willReturn(501);
        $address->expects($this->any())->method('getCustomerId')->willReturn(21);

        $this->replaceByMock('model', 'customer/address', $address);

        $quote = $this->getModelMock('sales/quote', array('getShippingAddress', 'collectTotals', 'save'));
        $quote->expects($this->never())->method('save');

        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getCustomerId', 'getQuote'));
        $subscription->expects($this->any())->method('getCustomerId')->willReturn(10);
        $subscription->expects($this->any())->method('getQuote')->willReturn($quote);

        $model = $this->getModelMock('sheep_subscription/service', array('canChangeShippingInformation'));
        $model->expects($this->once())->method('canChangeShippingInformation')->with($subscription)->willReturn(true);

        $actual = $model->setSubscriptionShippingAddress($subscription, 501);
        $this->assertNotNull($actual);
        $this->assertCount(1, $actual);
        $this->assertEquals('Specified customer address was not found.', $actual[0]);
    }


    public function testSetSubscriptionShippingAddressWithInvalidAddressData()
    {
        $address = $this->getModelMock('customer/address', array('load', 'getId', 'getCustomerId'));
        $address->expects($this->once())->method('load')->with(501)->willReturnSelf();
        $address->expects($this->any())->method('getId')->willReturn(501);
        $address->expects($this->any())->method('getCustomerId')->willReturn(10);

        $this->replaceByMock('model', 'customer/address', $address);

        $quoteAddress = $this->getModelMock('sales/quote', array(
                'importCustomerAddress', 'setSaveInAddressBook', 'getData', 'implodeStreetAddress', 'setCollectShippingRates', 'validate')
        );
        $quoteAddress->expects($this->once())->method('importCustomerAddress')->with($address)->willReturnSelf();
        $quoteAddress->expects($this->any())->method('getData')->willReturn(array('address data'));
        $quoteAddress->expects($this->once())->method('setSaveInAddressBook')->with(0);

        $quote = $this->getModelMock('sales/quote', array('getShippingAddress', 'collectTotals', 'save'));
        $quote->expects($this->any())->method('getShippingAddress')->willReturn($quoteAddress);
        $quote->expects($this->never())->method('collectTotals')->willReturnSelf();
        $quote->expects($this->never())->method('save');

        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getCustomerId', 'getQuote'));
        $subscription->expects($this->any())->method('getCustomerId')->willReturn(10);
        $subscription->expects($this->any())->method('getQuote')->willReturn($quote);

        $addressForm = $this->getModelMock('customer/form', array('setFormCode', 'setEntityType', 'setEntity', 'validateData'));
        $addressForm->expects($this->once())->method('setFormCode')->with('customer_address_edit')->willReturnSelf();
        $addressForm->expects($this->once())->method('setEntityType')->with('customer_address')->willReturnSelf();
        $addressForm->expects($this->once())->method('setEntity')->with($quoteAddress);
        $addressForm->expects($this->once())->method('validateData')->with(array('address data'))->willReturn(array('error'));
        $this->replaceByMock('model', 'customer/form', $addressForm);

        $model = $this->getModelMock('sheep_subscription/service', array('canChangeShippingInformation'));
        $model->expects($this->once())->method('canChangeShippingInformation')->with($subscription)->willReturn(true);

        $actual = $model->setSubscriptionShippingAddress($subscription, 501);
        $this->assertNotNull($actual);
        $this->assertCount(1, $actual);
        $this->assertEquals(array('error'), $actual);
    }


    public function testSetSubscriptionShippingAddressWithAddressErrors()
    {
        $address = $this->getModelMock('customer/address', array('load', 'getId', 'getCustomerId'));
        $address->expects($this->once())->method('load')->with(501)->willReturnSelf();
        $address->expects($this->any())->method('getId')->willReturn(501);
        $address->expects($this->any())->method('getCustomerId')->willReturn(10);

        $this->replaceByMock('model', 'customer/address', $address);

        $quoteAddress = $this->getModelMock('sales/quote', array(
                'importCustomerAddress', 'setSaveInAddressBook', 'getData', 'implodeStreetAddress', 'setCollectShippingRates', 'validate')
        );
        $quoteAddress->expects($this->once())->method('importCustomerAddress')->with($address)->willReturnSelf();
        $quoteAddress->expects($this->any())->method('getData')->willReturn(array('address data'));
        $quoteAddress->expects($this->once())->method('implodeStreetAddress');
        $quoteAddress->expects($this->once())->method('setCollectShippingRates')->with(true);
        $quoteAddress->expects($this->once())->method('validate')->willReturn(array('address error'));
        $quoteAddress->expects($this->once())->method('setSaveInAddressBook')->with(0);

        $quote = $this->getModelMock('sales/quote', array('getShippingAddress', 'collectTotals', 'save'));
        $quote->expects($this->any())->method('getShippingAddress')->willReturn($quoteAddress);
        $quote->expects($this->never())->method('collectTotals')->willReturnSelf();
        $quote->expects($this->never())->method('save');

        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getCustomerId', 'getQuote'));
        $subscription->expects($this->any())->method('getCustomerId')->willReturn(10);
        $subscription->expects($this->any())->method('getQuote')->willReturn($quote);

        $addressForm = $this->getModelMock('customer/form', array('setFormCode', 'setEntityType', 'setEntity', 'validateData'));
        $addressForm->expects($this->once())->method('setFormCode')->with('customer_address_edit')->willReturnSelf();
        $addressForm->expects($this->once())->method('setEntityType')->with('customer_address')->willReturnSelf();
        $addressForm->expects($this->once())->method('setEntity')->with($quoteAddress);
        $addressForm->expects($this->once())->method('validateData')->with(array('address data'))->willReturn(true);
        $this->replaceByMock('model', 'customer/form', $addressForm);

        $model = $this->getModelMock('sheep_subscription/service', array('canChangeShippingInformation'));
        $model->expects($this->once())->method('canChangeShippingInformation')->with($subscription)->willReturn(true);

        $actual = $model->setSubscriptionShippingAddress($subscription, 501);
        $this->assertNotNull($actual);
        $this->assertCount(1, $actual);
        $this->assertEquals(array('address error'), $actual);
    }


    public function testSetSubscriptionShippingAddressData()
    {
        $quoteAddress = $this->getModelMock('sales/quote_address', array(
            'setSaveInAddressBook', 'setSameAsBilling', 'setCustomerAddressId', 'implodeStreetAddress', 'setCollectShippingRates', 'validate')
        );
        $quoteAddress->expects($this->once())->method('setSaveInAddressBook')->with(0);
        $quoteAddress->expects($this->once())->method('setSameAsBilling')->with(0);
        $quoteAddress->expects($this->once())->method('setCustomerAddressId')->with(null);
        $quoteAddress->expects($this->once())->method('implodeStreetAddress');
        $quoteAddress->expects($this->once())->method('setCollectShippingRates')->with(true);
        $quoteAddress->expects($this->once())->method('validate')->willReturn(true);

        $quote = $this->getModelMock('sales/quote', array('getShippingAddress', 'collectTotals', 'save'));
        $quote->expects($this->any())->method('getShippingAddress')->willReturn($quoteAddress);
        $quote->expects($this->once())->method('collectTotals')->willReturnSelf();
        $quote->expects($this->once())->method('save');

        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getQuote'));
        $subscription->expects($this->any())->method('getQuote')->willReturn($quote);

        $model = $this->getModelMock('sheep_subscription/service', array('canChangeShippingInformation'));
        $model->expects($this->once())->method('canChangeShippingInformation')->willReturn(true);

        $addressData = array(

        );

        $requestData = $this->getMock('Zend_Controller_Request_Http');

        $addressForm = $this->getModelMock('customer/form',
            array('setFormCode', 'setEntityType', 'setEntity', 'extractData', 'prepareRequest', 'validateData', 'compactData', 'getAttributes'));
        $addressForm->expects($this->once())->method('setFormCode')->with('customer_address_edit')->willReturnSelf();
        $addressForm->expects($this->once())->method('setEntityType')->with('customer_address');
        $addressForm->expects($this->once())->method('setEntity')->with($quoteAddress);
        $addressForm->expects($this->once())->method('prepareRequest')->with($addressData)->willReturn($requestData);
        $addressForm->expects($this->once())->method('extractData')->with($requestData)->willReturn(array('address data'));
        $addressForm->expects($this->once())->method('validateData')->with(array('address data'))->willReturn(true);
        $addressForm->expects($this->once())->method('compactData')->with(array('address data'));
        $addressForm->expects($this->once())->method('getAttributes')->willReturn(array());
        $this->replaceByMock('model', 'customer/form', $addressForm);

        $actual = $model->setSubscriptionShippingAddressData($subscription, $addressData);
        $this->assertNotNull($actual);
        $this->assertCount(0, $actual);
    }

    
    public function testSetSubscriptionShippingMethod()
    {
        $address = $this->getModelMock('sales/quote_address', array('getShippingRateByCode', 'setShippingMethod'));
        $address->expects($this->once())->method('getShippingRateByCode')->willReturn(true);
        $address->expects($this->once())->method('setShippingMethod')->with('fedex_ground');

        $quote = $this->getModelMock('sales/quote', array('getShippingAddress', 'collectTotals', 'save'));
        $quote->expects($this->any())->method('getShippingAddress')->willReturn($address);
        $quote->expects($this->once())->method('collectTotals')->willReturnSelf();
        $quote->expects($this->once())->method('save');

        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getQuote'));
        $subscription->expects($this->any())->method('getQuote')->willReturn($quote);

        $model = $this->getModelMock('sheep_subscription/service', array('canChangeShippingInformation'));
        $model->expects($this->once())->method('canChangeShippingInformation')->with($subscription)->willReturn(true);

        $actual = $model->setSubscriptionShippingMethod($subscription, 'fedex_ground');
        $this->assertNotNull($actual);
        $this->assertCount(0, $actual);
    }


    public function testSetSubscriptionShippingMethodNotAllowed()
    {
        $quote = $this->getModelMock('sales/quote', array('getShippingAddress', 'collectTotals', 'save'));
        $quote->expects($this->never())->method('collectTotals')->willReturnSelf();
        $quote->expects($this->never())->method('save');

        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getQuote'));
        $subscription->expects($this->any())->method('getQuote')->willReturn($quote);

        $model = $this->getModelMock('sheep_subscription/service', array('canChangeShippingInformation'));
        $model->expects($this->once())->method('canChangeShippingInformation')->with($subscription)->willReturn(false);

        $actual = $model->setSubscriptionShippingMethod($subscription, 'fedex_ground');
        $this->assertNotNull($actual);
        $this->assertCount(1, $actual);
        $this->assertEquals('Shipping information cannot be changed on this subscription.', $actual[0]);
    }


    public function testSetSubscriptionShippingMethodWithInvalidMethod()
    {
        $address = $this->getModelMock('sales/quote_address', array('getShippingRateByCode', 'setShippingMethod'));
        $address->expects($this->once())->method('getShippingRateByCode')->willReturn('');
        $address->expects($this->never())->method('setShippingMethod')->with('fedex_ground');

        $quote = $this->getModelMock('sales/quote', array('getShippingAddress', 'collectTotals', 'save'));
        $quote->expects($this->any())->method('getShippingAddress')->willReturn($address);
        $quote->expects($this->never())->method('collectTotals')->willReturnSelf();
        $quote->expects($this->never())->method('save');

        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getQuote'));
        $subscription->expects($this->any())->method('getQuote')->willReturn($quote);

        $model = $this->getModelMock('sheep_subscription/service', array('canChangeShippingInformation'));
        $model->expects($this->once())->method('canChangeShippingInformation')->with($subscription)->willReturn(true);

        $actual = $model->setSubscriptionShippingMethod($subscription, 'fedex_ground');
        $this->assertNotNull($actual);
        $this->assertCount(1, $actual);
        $this->assertEquals('Invalid shipping method.', $actual[0]);
    }


    public function testCanChangePaymentInformation()
    {
        $helperMock = $this->getHelperMock('sheep_subscription', array('getIsAccountPaymentEditAllowed'));
        $helperMock->expects($this->atLeastOnce())->method('getIsAccountPaymentEditAllowed')->willReturn(true);
        $this->replaceByMock('helper', 'sheep_subscription', $helperMock);


        $subscription = $this->getModelMock('sheep_subscription/subscription', array('load', 'save'));

        // subscription without id
        $subscription->setStatus(Sheep_Subscription_Model_Subscription::STATUS_ACTIVE);
        $this->assertFalse($this->model->canChangePaymentInformation($subscription));

        $subscription->setId(100);
        $subscription->setStatus(Sheep_Subscription_Model_Subscription::STATUS_ACTIVE);
        $this->assertTrue($this->model->canChangePaymentInformation($subscription));

        $subscription->setId(100);
        $subscription->setStatus(Sheep_Subscription_Model_Subscription::STATUS_PAUSED);
        $this->assertTrue($this->model->canChangePaymentInformation($subscription));

        $subscription->setId(100);
        $subscription->setStatus(Sheep_Subscription_Model_Subscription::STATUS_CANCELLED);
        $this->assertTrue($this->model->canChangePaymentInformation($subscription));

        $subscription->setId(100);
        $subscription->setStatus(Sheep_Subscription_Model_Subscription::STATUS_EXPIRED);
        $this->assertTrue($this->model->canChangePaymentInformation($subscription));
    }


    public function testAddSubscriptionToCart()
    {
        $firstProduct = $this->getModelMock('catalog/product');
        $firstQuoteItem = $this->getModelMock('sales/quote_item', array('getProduct', 'getBuyRequest'));
        $firstQuoteItem->expects($this->once())->method('getProduct')->willReturn($firstProduct);
        $firstQuoteItem->expects($this->once())->method('getBuyRequest')->willReturn('first buy request');

        $secondProduct = $this->getModelMock('catalog/product');
        $secondQuoteItem = $this->getModelMock('sales/quote_item', array('getProduct', 'getBuyRequest'));
        $secondQuoteItem->expects($this->once())->method('getProduct')->willReturn($secondProduct);
        $secondQuoteItem->expects($this->once())->method('getBuyRequest')->willReturn('second buy request');


        $subscriptionQuote = $this->getModelMock('sales/model_quote', array('getAllVisibleItems'));
        $subscriptionQuote->expects($this->once())->method('getAllVisibleItems')->willReturn(array($firstQuoteItem, $secondQuoteItem));

        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getQuote'));
        $subscription->expects($this->once())->method('getQuote')->willReturn($subscriptionQuote);

        $quote = $this->getModelMock('sales/model_quote', array('addProduct', 'setTriggerRecollect', 'save', 'getId'));
        $quote->expects($this->at(0))->method('addProduct')->with($firstProduct, 'first buy request');
        $quote->expects($this->at(1))->method('addProduct')->with($secondProduct, 'second buy request');
        $quote->expects($this->once())->method('setTriggerRecollect')->with(1);
        $quote->expects($this->once())->method('save');
        $quote->expects($this->any())->method('getId')->willReturn(20001);

        $checkoutSession = $this->getModelMock('checkout/session', array('getQuote', 'setQuoteId', 'init'));
        $checkoutSession->expects($this->once())->method('getQuote')->willReturn($quote);
        $checkoutSession->expects($this->once())->method('setQuoteId')->with(20001);
        $this->replaceByMock('singleton', 'checkout/session', $checkoutSession);

        $model = $this->getModelMock('sheep_subscription/service', array('canChangePaymentInformation'));
        $model->expects($this->once())->method('canChangePaymentInformation')->with($subscription)->willReturn(true);
        $model->addSubscriptionToCart($subscription);
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Subscription payment information change is not allowed
     */
    public function testAddSubscriptionToCartNotAllowed()
    {
        $quote = $this->getModelMock('sales/model_quote', array('addProduct', 'setTriggerRecollect', 'save'));
        $quote->expects($this->never())->method('save');

        $checkoutSession = $this->getModelMock('checkout/session', array('getQuote', 'init'));
        $checkoutSession->expects($this->any())->method('getQuote')->willReturn($quote);
        $this->replaceByMock('singleton', 'checkout/session', $checkoutSession);

        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getQuote'));

        $model = $this->getModelMock('sheep_subscription/service', array('canChangePaymentInformation'));
        $model->expects($this->once())->method('canChangePaymentInformation')->with($subscription)->willReturn(false);
        $model->addSubscriptionToCart($subscription);
    }
}
