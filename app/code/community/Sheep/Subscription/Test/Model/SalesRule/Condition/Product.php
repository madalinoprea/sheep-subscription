<?php

/**
 * Class Sheep_Subscription_Test_Model_SalesRule_Condition_Subscriber
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2016, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_Model_SalesRule_Condition_Product
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Model_SalesRule_Condition_Product extends EcomDev_PHPUnit_Test_Case
{
    /** @var Sheep_Subscription_Model_SalesRule_Condition_Product $model */
    protected $model;


    protected function setUp()
    {
        $this->model = Mage::getModel('sheep_subscription/salesRule_condition_product');
    }



    public function loadAttributeOptions()
    {
        $this->model->loadAttributeOptions();

        $actual = $this->model->getAttributeOption();
        $this->assertNotNull($actual);
        $this->assertCount(1, $actual);
        $this->assertEquals('Subscription Product Skus', $actual[Sheep_Subscription_Model_SalesRule_Condition_Product::CONDITION_ATTRIBUTE_RECURRING_PRODUCT]);
    }


    public function testGetInputType()
    {
        $this->assertEquals('grid', $this->model->getInputType());
    }


    public function testGetValueElementType()
    {
        $this->assertEquals('text', $this->model->getValueElementType());
    }


    public function testValidateWithoutCustomer()
    {
        $model = $this->getModelMock('sheep_subscription/salesRule_condition_product', array('getCustomerId', 'getMatchingRecurringSkus'));
        $model->expects($this->once())->method('getCustomerId')->willReturn(null);
        $model->expects($this->never())->method('getMatchingRecurringSkus');

        $actual = $model->validate(new Varien_Object());
        $this->assertFalse($actual);
    }


    public function testValidate()
    {
        $matchingSkus = $this->getResourceModelMock('sales/quote_item_collection', array('load', 'getSize'));
        $matchingSkus->expects($this->never())->method('load');
        $matchingSkus->expects($this->once())->method('getSize')->willReturn(1);

        $model = $this->getModelMock('sheep_subscription/salesRule_condition_product', array('getCustomerId', 'getMatchingRecurringSkus', 'getValueParsed'));
        $model->expects($this->once())->method('getCustomerId')->willReturn(101);
        $model->expects($this->any())->method('getValueParsed')->willReturn(array('sku-1', 'sku-2'));
        $model->expects($this->once())->method('getMatchingRecurringSkus')->with(101, array('sku-1', 'sku-2'))->willReturn($matchingSkus);

        $actual = $model->validate(new Varien_Object());
        $this->assertTrue($actual);
    }


    public function testGetMatchingRecurringSkus()
    {
        $select = $this->getMock('Varien_Db_Select', array('where'), array(), '', false);
        $select->expects($this->once())->method('where')->with('p.sku IN (?)', array('sku-1', 'sku-2'));

        $matchingSkus = $this->getResourceModelMock('sales/quote_item_collection', array('load', 'join', 'getSelect', '_initSelect'));
        $matchingSkus->expects($this->never())->method('load');
        $matchingSkus->expects($this->once())->method('join')->with($this->arrayHasKey('p'), 'p.entity_id = product_id', array());
        $matchingSkus->expects($this->any())->method('getSelect')->willReturn($select);

        $helper = $this->getHelperMock('sheep_subscription/subscription', array('getCustomerRecurringProducts'));
        $helper->expects($this->once())->method('getCustomerRecurringProducts')->with(101)->willReturn($matchingSkus);
        $this->replaceByMock('helper', 'sheep_subscription/subscription', $helper);

        $actual = $this->model->getMatchingRecurringSkus(101, array('sku-1', 'sku-2'));
        $this->assertEquals($matchingSkus, $actual);
    }
}
