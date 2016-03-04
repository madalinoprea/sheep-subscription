<?php
/**
 * Class Sheep_Subscription_Test_Block_Adminhtml_Type_Edit
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_Block_Adminhtml_Type_Edit
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Block_Adminhtml_Type_Edit extends EcomDev_PHPUnit_Test_Case
{
    /** @var Sheep_Subscription_Block_Adminhtml_Type_Edit $block */
    protected $block;

    protected function setUp()
    {
        parent::setUp();
        $acl = $this->getModelMock('sheep_subscription/adminhtml_acl', array('canEditSubscriptionTypes'));
        $acl->expects($this->any())->method('canEditSubscriptionTypes')->willReturn(true);
        $this->replaceByMock('singleton', 'sheep_subscription/adminhtml_acl', $acl);

        $this->block = $this->getBlockMock('sheep_subscription/adminhtml_type_edit', array('getUrl', 'getLayout'));
    }

    public function testConstruct()
    {
        $this->assertEquals('sheep_subscription',
            EcomDev_Utils_Reflection::getRestrictedPropertyValue($this->block, '_blockGroup'));
        $this->assertEquals('adminhtml_type',
            EcomDev_Utils_Reflection::getRestrictedPropertyValue($this->block, '_controller'));
    }

    public function testGetModel()
    {
        $this->replaceRegistry('current_subscription_type', 'registry content');
        $actual = $this->block->getModel();
        $this->assertEquals('registry content', $actual);
    }

    public function testGetHeaderTextWithoutModel()
    {
        $actual = $this->block->getHeaderText();
        $this->assertEquals('New Subscription Type', $actual);
    }

    public function testGetHeaderWithModel()
    {
        $typeMock = $this->getModelMock('sheep_subscription/type', array('save'));
        $typeMock->setId(9999);

        $this->replaceRegistry('current_subscription_type', $typeMock);
        $actual = $this->block->getHeaderText();
        $this->assertEquals('Edit Subscription Type (ID: 9999)', $actual);
    }

    public function testGetBackUrl()
    {
        $block = $this->getBlockMock('sheep_subscription/adminhtml_type_edit', array('getUrl'));
        $block->expects($this->once())->method('getUrl')->with('*/*/index');
        $block->getBackUrl();
    }

    public function testGetDeleteUrl()
    {
        $block = $this->getBlockMock('sheep_subscription/adminhtml_type_edit', array('getUrl'));
        $block->expects($this->once())->method('getUrl')->with('*/*/delete', array('id' => ''));
        $block->getDeleteUrl();
    }

}
