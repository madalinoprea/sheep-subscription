<?php

/**
 * Class Sheep_Subscription_Test_Helper_Product
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, {2015}, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_Helper_Product
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Helper_Product extends EcomDev_PHPUnit_Test_Case
{
    /** @var Sheep_Subscription_Helper_Product $helper */
    protected $helper;

    protected function setUp()
    {
        parent::setUp();
        $this->helper = Mage::helper('sheep_subscription/product');
    }

    public function testGetEnabledProductTypeIds()
    {
        $actual = $this->helper->getEnabledProductTypeIds();
        $this->assertNotEmpty($actual);
        $this->assertContains('simple', $actual);
        $this->assertContains('virtual', $actual);
        $this->assertContains('configurable', $actual);
        $this->assertContains('bundle', $actual);
    }

    public function testIsEnabledForProductType()
    {
        $this->assertTrue($this->helper->isEnabledForProductType('simple'));
        $this->assertFalse($this->helper->isEnabledForProductType('unknown'));
    }

    public function testGetIsSubscriptionOptions()
    {
        $actual = $this->helper->getIsSubscriptionOptions();

        $this->assertNotEmpty($actual);
        $this->assertCount(3, $actual);
        $this->assertArrayHasKey(0, $actual);
        $this->assertEquals('One Time Purchase Only', $actual[0]);
        $this->assertArrayHasKey(1, $actual);
        $this->assertEquals('Subscription & One Time Purchase', $actual[1]);
        $this->assertArrayHasKey(2, $actual);
        $this->assertEquals('Subscription Only', $actual[2]);
    }

    public function testGetProductSubscriptionTypeIdsValues()
    {
        $helper = $this->getHelperMock('sheep_subscription/product', array('_getProductSubscriptionTypeIds'));
        $helper->expects($this->any())->method('_getProductSubscriptionTypeIds')->willReturn(array(1, 3, 5));

        $allProduct = $this->getModelMock('catalog/product', array('getPssSubscriptionType'));
        $allProduct->expects($this->any())->method('getPssSubscriptionType')->willReturn(Sheep_Subscription_Helper_Product::PRODUCT_SUBSCRIPTION_TYPES_ALL);
        $actual = $helper->getProductSubscriptionTypeIdsValues($allProduct);
        $this->assertNotEmpty($actual);
        $this->assertCount(1, $actual);
        $this->assertContains('A', $actual);

        $customProduct = $this->getModelMock('catalog/product', array('getPssSubscriptionType'));
        $customProduct->expects($this->any())->method('getPssSubscriptionType')->willReturn(Sheep_Subscription_Helper_Product::PRODUCT_SUBSCRIPTION_TYPES_CUSTOM);
        $actual = $helper->getProductSubscriptionTypeIdsValues($customProduct);
        $this->assertNotEmpty($actual);
        $this->assertEquals(array(1, 3, 5), $actual);

        $noneProduct = $this->getModelMock('catalog/product', array('getPssSubscriptionType'));
        $noneProduct->expects($this->any())->method('getPssSubscriptionType')->willReturn(Sheep_Subscription_Helper_Product::PRODUCT_SUBSCRIPTION_TYPES_NONE);
        $actual = $helper->getProductSubscriptionTypeIdsValues($noneProduct);
        $this->assertEmpty($actual);
    }

    public function testGetSubscriptionTypeOptions()
    {
        $typeHelper = $this->getHelperMock('sheep_subscription/type', array('getAvailableTypes'));
        $typeHelper->expects($this->once())->method('getAvailableTypes')->willReturn(
            array(
                new Varien_Object(array('title' => 'Type 1', 'id' => 1)),
                new Varien_Object(array('title' => 'Type 3', 'id' => 3)),
            )
        );
        $this->replaceByMock('helper', 'sheep_subscription/type', $typeHelper);

        $actual = $this->helper->getSubscriptionTypeOptions();
        $this->assertNotEmpty($actual);
        $this->assertCount(3, $actual);

        $this->assertEquals('All active subscription types', $actual[0]['label']);
        $this->assertEquals('A', $actual[0]['value']);

        $this->assertEquals('Type 1', $actual[1]['label']);
        $this->assertEquals(1, $actual[1]['value']);

        $this->assertEquals('Type 3', $actual[2]['label']);
        $this->assertEquals(3, $actual[2]['value']);
    }

    public function testIsSubscriptionProduct()
    {
        $productMock = $this->getModelMock('catalog/product', array('save'));

        $productMock->setPssIsSubscription(Sheep_Subscription_Helper_Product::PRODUCT_SUBSCRIPTION);
        $this->assertTrue($this->helper->isSubscriptionProduct($productMock));

        $productMock->setPssIsSubscription(Sheep_Subscription_Helper_Product::PRODUCT_SUBSCRIPTION_ONLY);
        $this->assertTrue($this->helper->isSubscriptionProduct($productMock));

        $productMock->setPssIsSubscription(Sheep_Subscription_Helper_Product::PRODUCT_PURCHASE_ONLY);
        $this->assertFalse($this->helper->isSubscriptionProduct($productMock));
    }



    /**
     * Test that product id is added as filter
     */
    public function testPrivateGetProductSubscriptionTypes()
    {
        $productMock = $this->getModelMock('catalog/product', array('getId'));
        $productMock->expects($this->once())->method('getId')->willReturn(100);

        $productTypeCollection = $this->getResourceModelMock('sheep_subscription/productType_collection', array('addProductToFilter'));
        $productTypeCollection->expects($this->once())->method('addProductToFilter')->with(100)->willReturnSelf();
        $this->replaceByMock('resource_model', 'sheep_subscription/productType_collection', $productTypeCollection);

        $actual = EcomDev_Utils_Reflection::invokeRestrictedMethod($this->helper, '_getProductSubscriptionTypes', array($productMock));
        $this->assertNotNull($actual);
        $this->assertInstanceOf('Sheep_Subscription_Model_Resource_ProductType_Collection', $actual);
    }

    /**
     * Tests that type ids associated to a product
     */
    public function testGetProductSubscriptionTypeIds()
    {
        $productMock = $this->getModelMock('catalog/product', array('getId'));
        $productMock->expects($this->any())->method('getId')->willReturn(100);

        $productTypeCollection = $this->getResourceModelMock('sheep_subscription/productType_collection', array('addFieldToSelect', 'getData'));
        $productTypeCollection->expects($this->once())->method('addFieldToSelect')->with('type_id');
        $productTypeCollection->expects($this->once())->method('getData')->willReturn(
            array(
                array('type_id' => 200),
                array('type_id' => 300)
            )
        );

        $helper = $this->getHelperMock('sheep_subscription/product', array('_getProductSubscriptionTypes'));
        $helper->expects($this->once())->method('_getProductSubscriptionTypes')->with($productMock)->willReturn($productTypeCollection);

        $actual = EcomDev_Utils_Reflection::invokeRestrictedMethod($helper, '_getProductSubscriptionTypeIds', array($productMock));
        $this->assertNotNull($actual);
        $this->assertCount(2, $actual);
        $this->assertContains(200, $actual);
        $this->assertContains(300, $actual);
    }

    public function testGetProductSubscriptionTypesWithNonSubscriptionProduct()
    {
        $productMock = $this->getModelMock('catalog/product', array('getStoreId', 'getPssSubscriptionType'));

        $helper = $this->getHelperMock('sheep_subscription/product', array('isSubscriptionProduct'));
        $helper->expects($this->once())->method('isSubscriptionProduct')->with($productMock)->willReturn(false);

        $actual = $helper->getProductSubscriptionTypes($productMock);
        $this->assertNotNull($actual);
        $this->assertInstanceOf('Varien_Data_Collection', $actual);
        $this->assertCount(0, $actual);
    }

    public function testGetProductSubscriptionTypesWithProductWithoutTypes()
    {
        $productMock = $this->getModelMock('catalog/product', array('getStoreId', 'getPssSubscriptionType'));
        $productMock->expects($this->once())->method('getPssSubscriptionType')->willReturn(Sheep_Subscription_Helper_Product::PRODUCT_SUBSCRIPTION_TYPES_NONE);

        $helper = $this->getHelperMock('sheep_subscription/product', array('isSubscriptionProduct'));
        $helper->expects($this->once())->method('isSubscriptionProduct')->with($productMock)->willReturn(true);

        $actual = $helper->getProductSubscriptionTypes($productMock);
        $this->assertNotNull($actual);
        $this->assertInstanceOf('Varien_Data_Collection', $actual);
        $this->assertCount(0, $actual);
    }

    /**
     * We test that all subscription types are retrieved and returned
     */
    public function testGetProductSubscriptionTypesWithProductWithAllTypes()
    {
        $productMock = $this->getModelMock('catalog/product', array('getStoreId', 'getPssSubscriptionType'));
        $productMock->expects($this->any())->method('getPssSubscriptionType')->willReturn(Sheep_Subscription_Helper_Product::PRODUCT_SUBSCRIPTION_TYPES_ALL);
        $productMock->expects($this->once())->method('getStoreId')->willReturn(200);

        // subscription types are loaded for product store (200)
        $typeHelper = $this->getHelperMock('sheep_subscription/type', array('getAvailableTypes'));
        $typeHelper->expects($this->once())->method('getAvailableTypes')->with(200)->willReturn('ALL SUBSCRIPTION TYPES');
        $this->replaceByMock('helper', 'sheep_subscription/type', $typeHelper);

        $helper = $this->getHelperMock('sheep_subscription/product', array('isSubscriptionProduct'));
        $helper->expects($this->once())->method('isSubscriptionProduct')->with($productMock)->willReturn(true);

        $actual = $helper->getProductSubscriptionTypes($productMock);
        $this->assertEquals('ALL SUBSCRIPTION TYPES', $actual);
    }

    /**
     *
     */
    public function testGetProductSubscriptionTypesWithProductWithCustomTypes()
    {
        $productMock = $this->getModelMock('catalog/product', array('getStoreId', 'getPssSubscriptionType'));
        $productMock->expects($this->any())->method('getPssSubscriptionType')->willReturn(Sheep_Subscription_Helper_Product::PRODUCT_SUBSCRIPTION_TYPES_CUSTOM);
        $productMock->expects($this->once())->method('getStoreId')->willReturn(200);

        // subscription types are filtered by id
        $typeCollection = $this->getResourceModelMock('sheep_subscription/type_collection', array('addFieldToFilter', 'load'));
        $typeCollection->expects($this->once())->method('addFieldToFilter')->with('id', array('in' => array(1, 3, 5)));

        $typeHelper = $this->getHelperMock('sheep_subscription/type', array('getAvailableTypes'));
        $typeHelper->expects($this->once())->method('getAvailableTypes')->with(200)->willReturn($typeCollection);
        $this->replaceByMock('helper', 'sheep_subscription/type', $typeHelper);

        $helper = $this->getHelperMock('sheep_subscription/product', array('isSubscriptionProduct', '_getProductSubscriptionTypeIds'));
        $helper->expects($this->once())->method('isSubscriptionProduct')->with($productMock)->willReturn(true);
        $helper->expects($this->once())->method('_getProductSubscriptionTypeIds')->with($productMock)->willReturn(array(1, 3, 5));

        $helper->getProductSubscriptionTypes($productMock);
    }

    public function testGetProductSubscriptionTypesWithProductWithUnknownType()
    {
        $productMock = $this->getModelMock('catalog/product', array('getStoreId', 'getPssSubscriptionType'));
        $productMock->expects($this->any())->method('getPssSubscriptionType')->willReturn('unknown');

        $helper = $this->getHelperMock('sheep_subscription/product', array('isSubscriptionProduct'));
        $helper->expects($this->once())->method('isSubscriptionProduct')->with($productMock)->willReturn(true);

        $actual = $helper->getProductSubscriptionTypes($productMock);
        $this->assertNotNull($actual);
        $this->assertInstanceOf('Varien_Data_Collection', $actual);
        $this->assertCount(0, $actual);
    }

    public function testSetProductSubscriptionTypesWithoutSavedProduct()
    {
        $productMock = $this->getModelMock('catalog/product', array('getId', 'setPssSubscriptionType'));
        $productMock->expects($this->once())->method('getId')->willReturn(null);
        $productMock->expects($this->never())->method('setPssSubscriptionType');

        $this->helper->setProductSubscriptionTypes($productMock, array());
    }

    public function testSetProductSubscriptionTypesWithProductWithAllTypes()
    {
        $previousTypeIds = array(1, 5);

        // pss_subscription_type is set
        $productMock = $this->getModelMock('catalog/product', array('getId', 'getPssSubscriptionType', 'setPssSubscriptionType'));
        $productMock->expects($this->any())->method('getId')->willReturn(100);
        $productMock->expects($this->any())->method('getPssSubscriptionType')->willReturn(Sheep_Subscription_Helper_Product::PRODUCT_SUBSCRIPTION_TYPES_ALL);
        $productMock->expects($this->once())->method('setPssSubscriptionType')->with(Sheep_Subscription_Helper_Product::PRODUCT_SUBSCRIPTION_TYPES_ALL);

        // Test that product subscription types are not added
        $productTypeModel = $this->getModelMock('sheep_subscription/productType', array('save'));
        $productTypeModel->expects($this->never())->method('save');
        $this->replaceByMock('model', 'sheep_subscription/productType', $productTypeModel);

        // Test that previous product subscription types are deleted (1, 5)
        $productTypes = $this->getResourceModelMock('sheep_subscription/productType_collection', array('addProductToFilter', 'addSubscriptionTypeFilter', 'walk'));
        $productTypes->expects($this->any())->method('addProductToFilter')->with(100);
        $productTypes->expects($this->once())->method('addSubscriptionTypeFilter')->with(array(1, 5));
        $productTypes->expects($this->once())->method('walk')->with('delete');
        $this->replaceByMock('resource_model', 'sheep_subscription/productType_collection', $productTypes);

        $subscriptionTypeIds = array(1, 3, Sheep_Subscription_Helper_Product::PRODUCT_SUBSCRIPTION_TYPES_ALL);
        $helper = $this->getHelperMock('sheep_subscription/product', array('_getProductSubscriptionTypeIds'));
        $helper->expects($this->once())->method('_getProductSubscriptionTypeIds')->willReturn($previousTypeIds);

        $helper->setProductSubscriptionTypes($productMock, $subscriptionTypeIds);
    }

    public function testSetProductSubscriptionTypesWithProductWithoutTypes()
    {
        $previousTypeIds = array(1, 5);

        // pss_subscription_type is set
        $productMock = $this->getModelMock('catalog/product', array('getId', 'getPssSubscriptionType', 'setPssSubscriptionType'));
        $productMock->expects($this->any())->method('getId')->willReturn(100);
        $productMock->expects($this->any())->method('getPssSubscriptionType')->willReturn(Sheep_Subscription_Helper_Product::PRODUCT_SUBSCRIPTION_TYPES_CUSTOM);
        $productMock->expects($this->once())->method('setPssSubscriptionType')->with(Sheep_Subscription_Helper_Product::PRODUCT_SUBSCRIPTION_TYPES_NONE);

        // Test that product subscription types are not added
        $productTypeModel = $this->getModelMock('sheep_subscription/productType', array('save'));
        $productTypeModel->expects($this->never())->method('save');
        $this->replaceByMock('model', 'sheep_subscription/productType', $productTypeModel);

        // Test that previous product subscription types are deleted (1, 5)
        $productTypes = $this->getResourceModelMock('sheep_subscription/productType_collection', array('addProductToFilter', 'addSubscriptionTypeFilter', 'walk'));
        $productTypes->expects($this->any())->method('addProductToFilter')->with(100);
        $productTypes->expects($this->once())->method('addSubscriptionTypeFilter')->with(array(1, 5));
        $productTypes->expects($this->once())->method('walk')->with('delete');
        $this->replaceByMock('resource_model', 'sheep_subscription/productType_collection', $productTypes);

        $subscriptionTypeIds = array();
        $helper = $this->getHelperMock('sheep_subscription/product', array('_getProductSubscriptionTypeIds'));
        $helper->expects($this->once())->method('_getProductSubscriptionTypeIds')->willReturn($previousTypeIds);

        $helper->setProductSubscriptionTypes($productMock, $subscriptionTypeIds);
    }

    public function testSetProductSubscriptionTypesWithProductCustomTypes()
    {
        $previousTypeIds = array(1, 5);

        // pss_subscription_type is set
        $productMock = $this->getModelMock('catalog/product', array('getId', 'getPssSubscriptionType', 'setPssSubscriptionType'));
        $productMock->expects($this->any())->method('getId')->willReturn(100);
        $productMock->expects($this->any())->method('getPssSubscriptionType')->willReturn(Sheep_Subscription_Helper_Product::PRODUCT_SUBSCRIPTION_TYPES_CUSTOM);
        $productMock->expects($this->once())->method('setPssSubscriptionType')->with(Sheep_Subscription_Helper_Product::PRODUCT_SUBSCRIPTION_TYPES_CUSTOM);

        // Test that product new subscription types are added (2 and 6)
        $productTypeModel = $this->getModelMock('sheep_subscription/productType', array('save', 'setProductId', 'setTypeId'));
        $productTypeModel->expects($this->at(0))->method('setProductId')->with(100);
        $productTypeModel->expects($this->at(1))->method('setTypeId')->with(2);
        $productTypeModel->expects($this->at(2))->method('save');

        $productTypeModel->expects($this->at(3))->method('setProductId')->with(100);
        $productTypeModel->expects($this->at(4))->method('setTypeId')->with(6);
        $productTypeModel->expects($this->at(5))->method('save');
        $this->replaceByMock('model', 'sheep_subscription/productType', $productTypeModel);

        // Test that previous product subscription types that are not kept are deleted (1)
        $productTypes = $this->getResourceModelMock('sheep_subscription/productType_collection', array('addProductToFilter', 'addSubscriptionTypeFilter', 'walk'));
        $productTypes->expects($this->any())->method('addProductToFilter')->with(100);
        $productTypes->expects($this->once())->method('addSubscriptionTypeFilter')->with(array(1));
        $productTypes->expects($this->once())->method('walk')->with('delete');
        $this->replaceByMock('resource_model', 'sheep_subscription/productType_collection', $productTypes);

        $subscriptionTypeIds = array(2, 5, 6);
        $helper = $this->getHelperMock('sheep_subscription/product', array('_getProductSubscriptionTypeIds'));
        $helper->expects($this->once())->method('_getProductSubscriptionTypeIds')->willReturn($previousTypeIds);

        $helper->setProductSubscriptionTypes($productMock, $subscriptionTypeIds);
    }

    /**
     * @covers Sheep_Subscription_Helper_Product::getProductSubscriptionTypePrices
     */
    public function testGetProductSubscriptionTypePrices()
    {
        $typePrices = $this->getResourceModelMock('sheep_subscription/productTypePrice_collection', array('addProductToFilter', 'load'));
        $typePrices->expects($this->once())->method('addProductToFilter')->with(100);
        $this->replaceByMock('resource_model', 'sheep_subscription/productTypePrice_collection', $typePrices);

        $productMock = $this->getModelMock('catalog/product', array('getId'));
        $productMock->expects($this->any())->method('getId')->willReturn(100);

        $actual = $this->helper->getProductSubscriptionTypePrices($productMock);
        $this->assertNotNull($actual);
        $this->assertEquals($typePrices, $actual);
    }


    /**
     * @covers Sheep_Subscription_Helper_Product::setProductSubscriptionTypePrices
     */
    public function testSetProductSubscriptionTypePrices()
    {
        $product = $this->getModelMock('catalog/product', array('getId', 'load', 'save'));
        $product->expects($this->any())->method('getId')->willReturn(100);

        $prices = array(
            array('type_id' => 5, 'discount' => 25, 'discount_percent' => 0, )
        );

        // Verify that model is correctly initialized
        $typePriceModel = $this->getModelMock('sheep_subscription/productTypePrice', array('setProductId', 'setTypeId', 'setDiscount', 'setDiscountPercent'));
        $typePriceModel->expects($this->once())->method('setProductId')->with(100);
        $typePriceModel->expects($this->once())->method('setTypeId')->with(5);
        $typePriceModel->expects($this->once())->method('setDiscount')->with(25);
        $typePriceModel->expects($this->once())->method('setDiscountPercent')->with(0);
        $this->replaceByMock('model', 'sheep_subscription/productTypePrice', $typePriceModel);

        // Verify that existing prices are deleted, new prices are added and saved
        $typePrices = $this->getResourceModelMock('sheep_subscription/productTypePrice_collection', array('addProductToFilter', 'walk', 'clear', 'getItemById', 'addItem', 'save', 'load'));
        $typePrices->expects($this->once())->method('addProductToFilter')->with(100);
        $typePrices->expects($this->once())->method('walk')->with('delete'); // current prices are deleted
        $typePrices->expects($this->once())->method('clear'); // current collection is cleared
        $typePrices->expects($this->once())->method('addItem')->with($typePriceModel); // new type price model is added
        $typePrices->expects($this->once())->method('save'); // collection is saved
        $this->replaceByMock('resource_model', 'sheep_subscription/productTypePrice_collection', $typePrices);

        // Verify that price type belongs to a type currently associated to our product
        $types = $this->getResourceModelMock('sheep_subscription/type_collection', array('getItemById', 'load'));
        $types->expects($this->once())->method('getItemById')->with(5)->willReturn(true);

        $helper = $this->getHelperMock('sheep_subscription/product', array('getProductSubscriptionTypes'));
        $helper->expects($this->once())->method('getProductSubscriptionTypes')->willReturn($types);

        $helper->setProductSubscriptionTypePrices($product, $prices);
    }

    /**
     * Verifies that prices specified for types that are no longer assigned to products are not added
     *
     * @covers Sheep_Subscription_Helper_Product::setProductSubscriptionTypePrices
     */
    public function testSetProductSubscriptionTypePricesWithOldType()
    {
        $product = $this->getModelMock('catalog/product', array('getId', 'load', 'save'));
        $product->expects($this->any())->method('getId')->willReturn(100);

        $prices = array(
            array('type_id' => 5, 'discount' => 25, 'discount_percent' => 0, )
        );

        // Verify that existing prices are deleted, new prices are added and saved
        $typePrices = $this->getResourceModelMock('sheep_subscription/productTypePrice_collection', array('addProductToFilter', 'walk', 'clear', 'getItemById', 'addItem', 'save', 'load'));
        $typePrices->expects($this->once())->method('addProductToFilter')->with(100);
        $typePrices->expects($this->once())->method('walk')->with('delete'); // current prices are deleted
        $typePrices->expects($this->once())->method('clear'); // current collection is cleared
        $typePrices->expects($this->never())->method('addItem'); // new type price model is never added
        $typePrices->expects($this->once())->method('save'); // collection is still saved
        $this->replaceByMock('resource_model', 'sheep_subscription/productTypePrice_collection', $typePrices);

        // Verify that price type doesn't belong to a type currently associated to our product
        $types = $this->getResourceModelMock('sheep_subscription/type_collection', array('getItemById', 'load'));
        $types->expects($this->once())->method('getItemById')->with(5)->willReturn(false);

        $helper = $this->getHelperMock('sheep_subscription/product', array('getProductSubscriptionTypes'));
        $helper->expects($this->once())->method('getProductSubscriptionTypes')->willReturn($types);

        $helper->setProductSubscriptionTypePrices($product, $prices);
    }

    /**
     * Verifies that prices that don't specify neither discount nor discount percent are ignored
     *
     * @covers Sheep_Subscription_Helper_Product::setProductSubscriptionTypePrices
     */
    public function testSetProductSubscriptionTypePricesWithoutDiscount()
    {
        $product = $this->getModelMock('catalog/product', array('getId', 'load', 'save'));
        $product->expects($this->any())->method('getId')->willReturn(100);

        $prices = array(
            array('type_id' => 5, 'discount' => 0, 'discount_percent' => 0, )
        );

        // Verify that existing prices are deleted, new prices are added and saved
        $typePrices = $this->getResourceModelMock('sheep_subscription/productTypePrice_collection', array('addProductToFilter', 'walk', 'clear', 'getItemById', 'addItem', 'save', 'load'));
        $typePrices->expects($this->once())->method('addProductToFilter')->with(100);
        $typePrices->expects($this->once())->method('walk')->with('delete'); // current prices are deleted
        $typePrices->expects($this->once())->method('clear'); // current collection is cleared
        $typePrices->expects($this->never())->method('addItem'); // new type price model is never added
        $typePrices->expects($this->once())->method('save'); // collection is still saved
        $this->replaceByMock('resource_model', 'sheep_subscription/productTypePrice_collection', $typePrices);

        // Verify that price type doesn't belong to a type currently associated to our product
        $types = $this->getResourceModelMock('sheep_subscription/type_collection', array('getItemById', 'load'));
        $types->expects($this->once())->method('getItemById')->with(5)->willReturn(true);

        $helper = $this->getHelperMock('sheep_subscription/product', array('getProductSubscriptionTypes'));
        $helper->expects($this->once())->method('getProductSubscriptionTypes')->willReturn($types);

        $helper->setProductSubscriptionTypePrices($product, $prices);
    }

    /**
     * Verifies that nothing is changed if product is invalid
     *
     * @covers Sheep_Subscription_Helper_Product::setProductSubscriptionTypePrices
     */
    public function testSetProductSubscriptionTypePricesWithoutProduct()
    {
        $product = $this->getModelMock('catalog/product', array('getId', 'load', 'save'));
        $product->expects($this->any())->method('getId')->willReturn(null);

        $prices = array(
            array('type_id' => 5, 'discount' => 25, 'discount_percent' => 0, )
        );

        // Verify that existing prices are deleted, new prices are added and saved
        $typePrices = $this->getResourceModelMock('sheep_subscription/productTypePrice_collection', array('addProductToFilter', 'walk', 'clear', 'getItemById', 'addItem', 'save', 'load'));
        $typePrices->expects($this->never())->method('addProductToFilter')->with(100);
        $typePrices->expects($this->never())->method('walk')->with('delete'); // current prices are deleted
        $typePrices->expects($this->never())->method('save'); // collection is still saved
        $this->replaceByMock('resource_model', 'sheep_subscription/productTypePrice_collection', $typePrices);

        $helper = $this->getHelperMock('sheep_subscription/product', array('getProductSubscriptionTypes'));

        $helper->setProductSubscriptionTypePrices($product, $prices);
    }

}
