<?php

/**
 * Class Sheep_Subscription_Test_Controller_SubscriptionTypeController
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers   Sheep_Subscription_Adminhtml_SubscriptionTypeController
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Controller_Adminhtml_SubscriptionTypeController extends Sheep_Util_Test_Case_Controller
{
    /** @var Sheep_Subscription_Adminhtml_SubscriptionTypeController $controller */
    protected $controller;

    protected function setUp()
    {
        parent::setUp();
        $this->replaceByMock('singleton', 'adminhtml/session', $this->getModelMock('admin/session', array('init')));

        $this->controller = $this->getControllerInstance('Sheep_Subscription_Adminhtml_SubscriptionTypeController',
            array('getLayout', 'loadLayout', 'renderLayout', 'getAcl', '_forward'));
    }


    protected function tearDown()
    {
        Mage::unregister('current_subscription_type');
        parent::tearDown();
    }


    public function testIndexAction()
    {
        $this->controller->indexAction();
        $this->getResponse()->sendResponse();

        $this->assertResponseHttpCode(200);
    }


    public function testExportCsvAction()
    {
        $this->controller->exportCsvAction();
        $this->getResponse()->sendResponse();

        $this->assertResponseHeaderEquals('Content-Disposition', 'attachment; filename="Subscription_Type_Export.csv"');
        $this->assertResponseHeaderEquals('Content-Type', 'application/octet-stream');
        $this->assertResponseHeaderSent('Content-Length');
    }


    public function testMassDeleteAction()
    {
        $this->getRequest()->setParam('ids', array(34, 27, 39));

        $typeCollection = $this->getResourceModelMock('sheep_subscription/type_collection', array('addFieldToFilter', 'walk'));
        $typeCollection->expects($this->once())->method('addFieldToFilter')->with('id', array(34, 27, 39));
        $typeCollection->expects($this->once())->method('walk')->with('delete');
        $this->replaceByMock('resource_model', 'sheep_subscription/type_collection', $typeCollection);

        $this->controller->massDeleteAction();
    }


    public function testEditAction()
    {
        $acl = $this->getModelMock('sheep_subscription/adminhtml_acl', array('canViewSubscriptionTypes'));
        $acl->expects($this->once())->method('canViewSubscriptionTypes')->willReturn(true);
        $this->controller->expects($this->once())->method('getAcl')->willReturn($acl);

        $this->getRequest()->setParam('id', 7);

        $typeMock = $this->getModelMock('sheep_subscription/type', array('load', 'getId', 'setData'));
        $typeMock->expects($this->once())->method('load')->with(7);
        $typeMock->expects($this->any())->method('getId')->willReturn(7);
        $this->replaceByMock('model', 'sheep_subscription/type', $typeMock);

        $this->controller->editAction();

        $actual = Mage::registry('current_subscription_type');
        $this->assertNotNull($actual);
        $this->assertInstanceOf('Sheep_Subscription_Model_Type', $actual);
        $this->assertEquals(7, $actual->getId());
    }


    public function testNewAction()
    {
        $this->controller->expects($this->once())->method('_forward')->with('edit');
        $this->controller->newAction();
    }


    public function testSaveAction()
    {
        $acl = $this->getModelMock('sheep_subscription/adminhtml_acl', array('canEditSubscriptionTypes'));
        $acl->expects($this->once())->method('canEditSubscriptionTypes')->willReturn(true);
        $this->controller->expects($this->once())->method('getAcl')->willReturn($acl);

        $this->getRequest()->setParam('id', 5);
        $this->getRequest()->setPost(array('title' => 'New Test Subscription Type'));

        $type = $this->getModelMock('sheep_subscription/type', array('load', 'getId', 'addData', 'save'));
        $type->expects($this->any())->method('getId')->willReturn(5);
        $type->expects($this->once())->method('load')->with(5);
        $type->expects($this->once())->method('addData')->with(array('title' => 'New Test Subscription Type'));
        $type->expects($this->once())->method('save');
        $this->replaceByMock('model', 'sheep_subscription/type', $type);

        $this->controller->saveAction();
    }


    public function testDeleteAction()
    {
        $acl = $this->getModelMock('sheep_subscription/adminhtml_acl', array('canEditSubscriptionTypes'));
        $acl->expects($this->once())->method('canEditSubscriptionTypes')->willReturn(true);
        $this->controller->expects($this->once())->method('getAcl')->willReturn($acl);

        $this->getRequest()->setParam('id', 8);

        $type = $this->getModelMock('sheep_subscription/type', array('load', 'getId', 'delete'));
        $type->expects($this->any())->method('getId')->willReturn(8);
        $type->expects($this->once())->method('load')->with(8);
        $type->expects($this->once())->method('delete');
        $this->replaceByMock('model', 'sheep_subscription/type', $type);


        $this->controller->deleteAction();
    }

}
