<?php

/**
 * Class Sheep_Subscription_Test_Block_Adminhtml_Catalog_Product_Edit_Tab_Subscription
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, {2015}, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_Block_Adminhtml_Catalog_Product_Edit_Tab_Subscription
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Block_Adminhtml_Catalog_Product_Edit_Tab_Subscription extends EcomDev_PHPUnit_Test_Case
{
    /**
     * Tests that getProduct returns value from pss_current_product registry
     */
    public function testGetProduct()
    {
        $object = $this->getBlockMock('sheep_subscription/adminhtml_catalog_product_edit_tab_subscription', array('toHtml'));
        $this->replaceRegistry('pss_current_product', 'registered product');
        $actual = $object->getProduct();
        $this->assertEquals('registered product', $actual);
    }


    /**
     * Tests that _prepareForm adds fields for is subscription and subscription type, also verifies that
     * sheep_subscription/product helper is used to populate options for these select fields
     */
    public function testPrepareForm()
    {
        $object = $this->getBlockMock('sheep_subscription/adminhtml_catalog_product_edit_tab_subscription', array('addConfigurationFieldset', 'addPriceFieldset'));
        $object->expects($this->once())->method('addConfigurationFieldset');
        $object->expects($this->once())->method('addPriceFieldset');

        EcomDev_Utils_Reflection::invokeRestrictedMethod($object, '_prepareForm');

        $form = $object->getForm();
        $this->assertNotNull($form);
        $this->assertInstanceOf('Varien_Data_Form', $form);
    }


    public function testAddConfigurationFieldset()
    {
        $acl = $this->getModelMock('sheep_subscription/adminhtml_acl', array('canViewProductSubscriptionConfiguration'));
        $acl->expects($this->once())->method('canViewProductSubscriptionConfiguration')->willReturn(true);
        $this->replaceByMock('singleton', 'sheep_subscription/adminhtml_acl', $acl);

        $productMock = $this->getModelMock('catalog/product', array('getPssIsSubscription'));
        $productMock->expects($this->any())->method('getPssIsSubscription')->willReturn(10);

        $helperMock = $this->getHelperMock('sheep_subscription/product', array('getIsSubscriptionOptions', 'getSubscriptionTypeOptions', 'getProductSubscriptionTypeIdsValues'));
        $helperMock->expects($this->once())->method('getIsSubscriptionOptions')->willReturn('is subscription options');
        $helperMock->expects($this->once())->method('getSubscriptionTypeOptions')->willReturn('subscription type options');
        $helperMock->expects($this->once())->method('getProductSubscriptionTypeIdsValues')
            ->with($productMock)
            ->willReturn('product subscription type ids');
        $this->replaceByMock('helper', 'sheep_subscription/product', $helperMock);

        $object = $this->getBlockMock('sheep_subscription/adminhtml_catalog_product_edit_tab_subscription', array('getProduct', 'getForm'));
        $object->expects($this->any())->method('getForm')->willReturn(new Varien_Data_Form());
        $object->expects($this->any())->method('getProduct')->willReturn($productMock);

        $object->addConfigurationFieldset();

        $form = $object->getForm();
        $this->assertNotNull($form);

        $isSubscription = $form->getElement('pss_is_subscription');
        $this->assertNotNull($isSubscription);
        $this->assertEquals('pss_is_subscription', $isSubscription->getName());
        $this->assertEquals('Is Subscription', $isSubscription->getLabel());
        $this->assertEquals('select', $isSubscription->getType());
        $this->assertFalse($isSubscription->getRequired());
        $this->assertEquals(10, $isSubscription->getValue());

        $subscriptionType = $form->getElement('pss_subscription_type');
        $this->assertNotNull($subscriptionType);
        $this->assertEquals('pss_subscription_type[]', $subscriptionType->getName());
        $this->assertEquals('Subscription Types', $subscriptionType->getLabel());
        $this->assertFalse($isSubscription->getRequired());
        $this->assertEquals('select', $subscriptionType->getType());
        $this->assertEquals('multiple', $subscriptionType->getExtType());
        $this->assertEquals('subscription type options', $subscriptionType->getValues());
        $this->assertEquals('product subscription type ids', $subscriptionType->getValue());
    }


    public function testAddConfigurationFieldsetWithoutPrivileges()
    {
        $acl = $this->getModelMock('sheep_subscription/adminhtml_acl', array('canViewProductSubscriptionConfiguration'));
        $acl->expects($this->once())->method('canViewProductSubscriptionConfiguration')->willReturn(false);
        $this->replaceByMock('singleton', 'sheep_subscription/adminhtml_acl', $acl);

        $productMock = $this->getModelMock('catalog/product', array('getPssIsSubscription'));
        $productMock->expects($this->any())->method('getPssIsSubscription')->willReturn(10);

        $helperMock = $this->getHelperMock('sheep_subscription/product', array('getIsSubscriptionOptions', 'getSubscriptionTypeOptions', 'getProductSubscriptionTypeIdsValues'));
        $helperMock->expects($this->never())->method('getIsSubscriptionOptions')->willReturn('is subscription options');
        $helperMock->expects($this->never())->method('getSubscriptionTypeOptions')->willReturn('subscription type options');
        $helperMock->expects($this->never())->method('getProductSubscriptionTypeIdsValues')
            ->with($productMock)
            ->willReturn('product subscription type ids');
        $this->replaceByMock('helper', 'sheep_subscription/product', $helperMock);

        $object = $this->getBlockMock('sheep_subscription/adminhtml_catalog_product_edit_tab_subscription', array('getProduct', 'getForm'));
        $object->expects($this->any())->method('getForm')->willReturn(new Varien_Data_Form());
        $object->expects($this->any())->method('getProduct')->willReturn($productMock);

        $object->addConfigurationFieldset();

        $form = $object->getForm();
        $this->assertNotNull($form);

        $isSubscription = $form->getElement('pss_is_subscription');
        $this->assertNull($isSubscription);

        $subscriptionType = $form->getElement('pss_subscription_type');
        $this->assertNull($subscriptionType);
    }


    public function testAddPriceFieldset()
    {
        $acl = $this->getModelMock('sheep_subscription/adminhtml_acl', array('canViewProductSubscriptionPrices'));
        $acl->expects($this->once())->method('canViewProductSubscriptionPrices')->willReturn(true);
        $this->replaceByMock('singleton', 'sheep_subscription/adminhtml_acl', $acl);


        $productMock = $this->getModelMock('catalog/product', array('getPssIsSubscription'));
        $productMock->expects($this->any())->method('getPssIsSubscription')->willReturn(10);

        $helperMock = $this->getHelperMock('sheep_subscription/product', array('isSubscriptionProduct', 'getIsSubscriptionOptions', 'getSubscriptionTypeOptions', 'getProductSubscriptionTypeIdsValues'));
        $helperMock->expects($this->once())->method('isSubscriptionProduct')->with($productMock)->willReturn(true);
        $this->replaceByMock('helper', 'sheep_subscription/product', $helperMock);


        $layoutMock = $this->getModelMock('core/layout', array('createBlock'));
        $layoutMock->expects($this->once())->method('createBlock')->with('sheep_subscription/adminhtml_catalog_product_edit_tab_subscription_price')->willReturn(
            $this->getBlockMock('sheep_subscription/adminhtml_catalog_product_edit_tab_subscription_price')
        );

        $object = $this->getBlockMock('sheep_subscription/adminhtml_catalog_product_edit_tab_subscription', array('getProduct', 'getForm', 'getLayout'));
        $object->expects($this->any())->method('getForm')->willReturn(new Varien_Data_Form());
        $object->expects($this->any())->method('getProduct')->willReturn($productMock);
        $object->expects($this->once())->method('getLayout')->willReturn($layoutMock);

        $object->addPriceFieldset();

        $form = $object->getForm();
        $this->assertNotNull($form);

        $typePrice = $form->getElement('pss_subscription_type_price');
        $this->assertNotNull($typePrice);
        $this->assertInstanceOf('Sheep_Subscription_Block_Adminhtml_Catalog_Product_Edit_Tab_Subscription_Price', $typePrice->getRenderer());
    }


    public function testAddPriceFieldsetWithoutPrivileges()
    {
        $acl = $this->getModelMock('sheep_subscription/adminhtml_acl', array('canViewProductSubscriptionPrices'));
        $acl->expects($this->once())->method('canViewProductSubscriptionPrices')->willReturn(false);
        $this->replaceByMock('singleton', 'sheep_subscription/adminhtml_acl', $acl);


        $productMock = $this->getModelMock('catalog/product', array('getPssIsSubscription'));
        $productMock->expects($this->any())->method('getPssIsSubscription')->willReturn(10);

        $helperMock = $this->getHelperMock('sheep_subscription/product', array('isSubscriptionProduct', 'getIsSubscriptionOptions', 'getSubscriptionTypeOptions', 'getProductSubscriptionTypeIdsValues'));
        $helperMock->expects($this->never())->method('isSubscriptionProduct')->with($productMock)->willReturn(true);
        $this->replaceByMock('helper', 'sheep_subscription/product', $helperMock);


        $layoutMock = $this->getModelMock('core/layout', array('createBlock'));
        $layoutMock->expects($this->never())->method('createBlock')->with('sheep_subscription/adminhtml_catalog_product_edit_tab_subscription_price')->willReturn(
            $this->getBlockMock('sheep_subscription/adminhtml_catalog_product_edit_tab_subscription_price')
        );

        $object = $this->getBlockMock('sheep_subscription/adminhtml_catalog_product_edit_tab_subscription', array('getProduct', 'getForm', 'getLayout'));
        $object->expects($this->any())->method('getForm')->willReturn(new Varien_Data_Form());
        $object->expects($this->any())->method('getProduct')->willReturn($productMock);
        $object->expects($this->never())->method('getLayout')->willReturn($layoutMock);

        $object->addPriceFieldset();

        $form = $object->getForm();
        $this->assertNotNull($form);

        $typePrice = $form->getElement('pss_subscription_type_price');
        $this->assertNull($typePrice);
    }
}
