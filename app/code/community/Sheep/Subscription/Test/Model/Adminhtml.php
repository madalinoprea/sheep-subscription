<?php
/**
 * Class Sheep_Subscription_Test_Model_Adminhtml
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_Model_Adminhtml
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Model_Adminhtml extends EcomDev_PHPUnit_Test_Case
{

    /**
     * @covers Sheep_Subscription_Model_Adminhtml::addSubscriptionTabs
     */
    public function testAddSubscriptionTabs()
    {
        $productTabsBlock = $this->getBlockMock('adminhtml/catalog_product_edit_tabs', array('getId'));
        $productTabsBlock->expects($this->any())->method('getId')->willReturn('product_info_tabs');
        $customerTabsBlock = $this->getBlockMock('adminhtml/customer_edit_tabs', array('getId'));
        $customerTabsBlock->expects($this->any())->method('getId')->willReturn('customer_info_tabs');

        $model = $this->getModelMock('sheep_subscription/adminhtml', array('addSubscriptionTabOnProductInfoTabs', 'addSubscriptionTabOnCustomerTabs'));
        $model->expects($this->once())->method('addSubscriptionTabOnProductInfoTabs')->with($productTabsBlock);
        $model->expects($this->once())->method('addSubscriptionTabOnCustomerTabs')->with($customerTabsBlock);

        $model->addSubscriptionTabs($productTabsBlock);
        $model->addSubscriptionTabs($customerTabsBlock);
    }


    /**
     * @covers Sheep_Subscription_Model_Adminhtml::addSubscriptionTabOnProductInfoTabs
     */
    public function testAddSubscriptionTabOnProductInfoTabs()
    {
        $product = $this->getModelMock('catalog/product', array('getId', 'getTypeId'));
        $product->expects($this->any())->method('getId')->willReturn(100);
        $product->expects($this->any())->method('getTypeId')->willReturn('configurable');

        $helperMock = $this->getHelperMock('sheep_subscription/product', array('isEnabledForProductType'));
        $helperMock->expects($this->once())->method('isEnabledForProductType')->with('configurable')->willReturn(true);
        $this->replaceByMock('helper', 'sheep_subscription/product', $helperMock);

        $block = $this->getBlockMock('adminhtml/catalog_product_edit_tabs', array('getProduct', 'addTabAfter', 'getUrl'));
        $block->expects($this->any())->method('getProduct')->willReturn($product);
        $block->expects($this->once())->method('getUrl')->with('adminhtml/subscription/configurationProductTab', array('product_id' => 100));
        $block->expects($this->once())->method('addTabAfter')->with(
            'sheep_subscription',
            $this->logicalAnd($this->arrayHasKey('label'), $this->arrayHasKey('url'), $this->arrayHasKey('class')),
            'inventory'
        );

        $acl = $this->getModelMock('sheep_subscription/adminhtml_acl', array('canShowProductSubscriptionTab'));
        $acl->expects($this->once())->method('canShowProductSubscriptionTab')->willReturn(true);
        $model = $this->getModelMock('sheep_subscription/adminhtml', array('getAdminAcl'));
        $model->expects($this->any())->method('getAdminAcl')->willReturn($acl);

        $model->addSubscriptionTabOnProductInfoTabs($block);
    }


    /**
     * @covers Sheep_Subscription_Model_Adminhtml::addSubscriptionTabOnProductInfoTabs
     */
    public function testAddSubscriptionTabOnProductInfoTabsForUnsupportedProductType()
    {
        $product = $this->getModelMock('catalog/product', array('getId', 'getTypeId'));
        $product->expects($this->any())->method('getId')->willReturn(100);
        $product->expects($this->any())->method('getTypeId')->willReturn('configurable');

        $helperMock = $this->getHelperMock('sheep_subscription/product', array('isEnabledForProductType'));
        $helperMock->expects($this->once())->method('isEnabledForProductType')->with('configurable')->willReturn(false);
        $this->replaceByMock('helper', 'sheep_subscription/product', $helperMock);

        $block = $this->getBlockMock('adminhtml/catalog_product_edit_tabs', array('getProduct', 'addTabAfter', 'getUrl'));
        $block->expects($this->any())->method('getProduct')->willReturn($product);
        $block->expects($this->never())->method('addTabAfter');

        $acl = $this->getModelMock('sheep_subscription/adminhtml_acl', array('canShowProductSubscriptionTab'));
        $acl->expects($this->once())->method('canShowProductSubscriptionTab')->willReturn(true);
        $model = $this->getModelMock('sheep_subscription/adminhtml', array('getAdminAcl'));
        $model->expects($this->any())->method('getAdminAcl')->willReturn($acl);

        $model->addSubscriptionTabOnProductInfoTabs($block);
    }


    /**
     * @covers Sheep_Subscription_Model_Adminhtml::addSubscriptionTabOnProductInfoTabs
     */
    public function testAddSubscriptionTabOnProductInfoTabsForForbiddenSession()
    {
        $product = $this->getModelMock('catalog/product', array('getId', 'getTypeId'));
        $product->expects($this->any())->method('getId')->willReturn(100);
        $product->expects($this->any())->method('getTypeId')->willReturn('configurable');

        $helperMock = $this->getHelperMock('sheep_subscription/product', array('isEnabledForProductType'));
        $helperMock->expects($this->never())->method('isEnabledForProductType');
        $this->replaceByMock('helper', 'sheep_subscription/product', $helperMock);

        $block = $this->getBlockMock('adminhtml/catalog_product_edit_tabs', array('getProduct', 'addTabAfter', 'getUrl'));
        $block->expects($this->any())->method('getProduct')->willReturn($product);
        $block->expects($this->never())->method('addTabAfter');

        $acl = $this->getModelMock('sheep_subscription/adminhtml_acl', array('canShowProductSubscriptionTab'));
        $acl->expects($this->once())->method('canShowProductSubscriptionTab')->willReturn(false);
        $model = $this->getModelMock('sheep_subscription/adminhtml', array('getAdminAcl'));
        $model->expects($this->any())->method('getAdminAcl')->willReturn($acl);

        $model->addSubscriptionTabOnProductInfoTabs($block);
    }


    /**
     * @covers Sheep_Subscription_Model_Adminhtml::addSubscriptionTabOnCustomerTabs
     */
    public function testAddSubscriptionTabOnCustomerTabs()
    {
        $customer = $this->getModelMock('customer/customer', array('getId'));
        $customer->expects($this->any())->method('getId')->willReturn(102);
        $this->replaceRegistry('current_customer', $customer);

        $block = $this->getBlockMock('adminhtml/customer_edit_tabs', array('addTabAfter', 'getUrl', 'assign', 'getTabsIds', 'setActiveTab'));
        $block->expects($this->once())->method('addTabAfter')->with(
            'sheep_subscription',
            $this->logicalAnd($this->arrayHasKey('label'), $this->arrayHasKey('url'), $this->arrayHasKey('class'), $this->arrayHasKey('active')),
            'orders'
        );
        $block->expects($this->once())->method('getUrl')->with('adminhtml/subscription/customerTab', array('customer_id' => 102));

        $acl = $this->getModelMock('sheep_subscription/adminhtml_acl', array('canShowCustomerSubscriptionTab'));
        $acl->expects($this->once())->method('canShowCustomerSubscriptionTab')->willReturn(true);
        $model = $this->getModelMock('sheep_subscription/adminhtml', array('getAdminAcl'));
        $model->expects($this->any())->method('getAdminAcl')->willReturn($acl);

        $model->addSubscriptionTabOnCustomerTabs($block);
    }


    /**
     * @covers Sheep_Subscription_Model_Adminhtml::addSubscriptionTabOnCustomerTabs
     */
    public function testAddSubscriptionTabOnCustomerTabsWithoutPrivileges()
    {
        $customer = $this->getModelMock('customer/customer', array('getId'));
        $customer->expects($this->any())->method('getId')->willReturn(102);
        $this->replaceRegistry('current_customer', $customer);

        $block = $this->getBlockMock('adminhtml/customer_edit_tabs', array('addTabAfter', 'getUrl', 'assign', 'getTabsIds', 'setActiveTab'));
        $block->expects($this->never())->method('addTabAfter');

        $acl = $this->getModelMock('sheep_subscription/adminhtml_acl', array('canShowCustomerSubscriptionTab'));
        $acl->expects($this->once())->method('canShowCustomerSubscriptionTab')->willReturn(false);
        $model = $this->getModelMock('sheep_subscription/adminhtml', array('getAdminAcl'));
        $model->expects($this->any())->method('getAdminAcl')->willReturn($acl);

        $model->addSubscriptionTabOnCustomerTabs($block);
    }
}
