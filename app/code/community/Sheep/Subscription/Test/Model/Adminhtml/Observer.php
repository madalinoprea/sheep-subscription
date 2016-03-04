<?php


/**
 * Class Sheep_Subscription_Test_Model_Adminhtml_Observer
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_Model_Adminhtml_Observer
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Model_Adminhtml_Observer extends EcomDev_PHPUnit_Test_Case
{
    /** @var  Sheep_Subscription_Model_Adminhtml_Observer */
    protected $model;


    protected function setUp()
    {
        $this->model = Mage::getModel('sheep_subscription/adminhtml_observer');
    }


    public function testAddSubscriptionTabs()
    {
        $block = $this->getBlockMock('core/template');

        $adminModel = $this->getModelMock('sheep_subscription/adminhtml', array('addSubscriptionTabs'));
        $adminModel->expects($this->once())->method('addSubscriptionTabs')->with($block);
        $this->replaceByMock('model', 'sheep_subscription/adminhtml', $adminModel);

        $observer = new Varien_Event_Observer();
        $observer->setBlock($block);

        $this->model->addSubscriptionTabs($observer);
    }


    /**
     * Tests that request data is passed on product model to be accessed later
     */
    public function testPrepareProductSave()
    {

        $request = $this->getMock('Mage_Core_Controller_Request_Http', array('getPost'));
        $request->expects($this->any())->method('getPost')->with('product')->willReturn(array(
            'pss_subscription_type' => 'subscription data',
            'pss_subscription_type_price' => 'type price data'
        ));

        $product = $this->getModelMock('catalog/product', array('setPssSubscriptionData', 'setPssSubscriptionTypePriceData'));
        $product->expects($this->once())->method('setPssSubscriptionData')->with('subscription data');
        $product->expects($this->once())->method('setPssSubscriptionTypePriceData')->with('type price data');

        $observer = new Varien_Event_Observer();
        $observer->setEvent(new Varien_Object(array(
            'request' => $request,
            'product' => $product
        )));

        $acl = $this->getModelMock('sheep_subscription/adminhtml_acl', array('canEditProductSubscriptionConfiguration', 'canEditProductSubscriptionPrices'));
        $acl->expects($this->once())->method('canEditProductSubscriptionConfiguration')->willReturn(true);
        $acl->expects($this->once())->method('canEditProductSubscriptionPrices')->willReturn(true);

        $model = $this->getModelMock('sheep_subscription/adminhtml_observer', array('getAcl'));
        $model->expects($this->any())->method('getAcl')->willReturn($acl);

        $model->prepareProductSave($observer);
    }


    /**
     * Tests that request data is passed on product model to be accessed later only if privileges are met
     */
    public function testPrepareProductSaveWithoutPrivileges()
    {

        $request = $this->getMock('Mage_Core_Controller_Request_Http', array('getPost'));
        $request->expects($this->any())->method('getPost')->with('product')->willReturn(array(
            'pss_subscription_type' => 'subscription data',
            'pss_subscription_type_price' => 'type price data'
        ));

        $product = $this->getModelMock('catalog/product', array('setPssSubscriptionData', 'setPssSubscriptionTypePriceData'));
        $product->expects($this->never())->method('setPssSubscriptionData');
        $product->expects($this->once())->method('setPssSubscriptionTypePriceData')->with('type price data');

        $observer = new Varien_Event_Observer();
        $observer->setEvent(new Varien_Object(array(
            'request' => $request,
            'product' => $product
        )));

        $acl = $this->getModelMock('sheep_subscription/adminhtml_acl', array('canEditProductSubscriptionConfiguration', 'canEditProductSubscriptionPrices'));
        $acl->expects($this->once())->method('canEditProductSubscriptionConfiguration')->willReturn(false);
        $acl->expects($this->once())->method('canEditProductSubscriptionPrices')->willReturn(true);

        $model = $this->getModelMock('sheep_subscription/adminhtml_observer', array('getAcl'));
        $model->expects($this->any())->method('getAcl')->willReturn($acl);

        $model->prepareProductSave($observer);
    }


    public function testSaveProductSubscriptionTypes()
    {
        $product = $this->getModelMock('catalog/product', array('getId', 'getPssSubscriptionData', 'getPssSubscriptionTypePriceData'));
        $product->expects($this->any())->method('getId')->willReturn(100);
        $product->expects($this->any())->method('getPssSubscriptionData')->willReturn(array('subscription' => 'data'));
        $product->expects($this->any())->method('getPssSubscriptionTypePriceData')->willReturn(array('subscription type' => 'prices'));

        $observer = new Varien_Event_Observer();
        $observer->setProduct($product);

        $helperMock = $this->getHelperMock('sheep_subscription/product', array('setProductSubscriptionTypes', 'setProductSubscriptionTypePrices'));
        $helperMock->expects($this->once())->method('setProductSubscriptionTypes')->with($product, array('subscription' => 'data'));
        $helperMock->expects($this->once())->method('setProductSubscriptionTypePrices')->with($product, array('subscription type' => 'prices'));
        $this->replaceByMock('helper', 'sheep_subscription/product', $helperMock);

        $this->model->saveProductSubscriptionTypes($observer);
    }


    public function testSaveProductSubscriptionTypesWithoutProduct()
    {
        $product = $this->getModelMock('catalog/product', array('getId', 'getPssSubscriptionData', 'getPssSubscriptionTypePriceData'));
        $product->expects($this->any())->method('getId')->willReturn(null);
        $product->expects($this->any())->method('getPssSubscriptionData')->willReturn(array('subscription' => 'data'));
        $product->expects($this->any())->method('getPssSubscriptionTypePriceData')->willReturn(array('subscription type' => 'prices'));

        $observer = new Varien_Event_Observer();
        $observer->setProduct($product);

        $helperMock = $this->getHelperMock('sheep_subscription/product', array('setProductSubscriptionTypes', 'setProductSubscriptionTypePrices'));
        $helperMock->expects($this->never())->method('setProductSubscriptionTypes')->with($product, array('subscription' => 'data'));
        $helperMock->expects($this->never())->method('setProductSubscriptionTypePrices')->with($product, array('subscription type' => 'prices'));
        $this->replaceByMock('helper', 'sheep_subscription/product', $helperMock);

        $this->model->saveProductSubscriptionTypes($observer);
    }

}
