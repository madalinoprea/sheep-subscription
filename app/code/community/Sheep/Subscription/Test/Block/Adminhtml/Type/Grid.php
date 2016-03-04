<?php
/**
 * Class Sheep_Subscription_Test_Block_Adminhtml_Type_Grid
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_Block_Adminhtml_Type_Grid
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Block_Adminhtml_Type_Grid extends EcomDev_PHPUnit_Test_Case
{
    /** @var Sheep_Subscription_Block_Adminhtml_Type_Grid $block */
    protected $block;

    protected function setUp()
    {
        parent::setUp();
        $this->replaceByMock('singleton', 'adminhtml/session', $this->getModelMock('admin/session', array('init')));

        $this->block = $this->getBlockMock('sheep_subscription/adminhtml_type_grid', array('getUrl', 'getLayout'));

        $layoutMock = $this->getModelMock('core/layout', array('createBlock'));
        $layoutMock->expects($this->any())->method('createBlock')->will($this->returnValue($this->getBlockMock('core/text', array('toHtml'))));
        $this->block->expects($this->any())->method('getLayout')->will($this->returnValue($layoutMock));
    }

    public function testConstruct()
    {
        $this->assertEquals('grid_id', $this->block->getId());
        $this->assertEquals('asc',  EcomDev_Utils_Reflection::getRestrictedPropertyValue($this->block, '_defaultDir'));
        $this->assertEquals(true,  EcomDev_Utils_Reflection::getRestrictedPropertyValue($this->block, '_saveParametersInSession'));
    }

    public function testPrepareCollection()
    {
        EcomDev_Utils_Reflection::invokeRestrictedMethod($this->block, '_prepareCollection');
        $actual = $this->block->getCollection();
        $this->assertNotNull($actual);
        $this->assertInstanceOf('Sheep_Subscription_Model_Resource_Type_Collection', $actual);
    }

    public function testPrepareColumns()
    {
        EcomDev_Utils_Reflection::invokeRestrictedMethod($this->block, '_prepareColumns');
        $actual = $this->block->getColumns();

        $this->assertNotEmpty($actual);
        $this->assertArrayHasKey('title', $actual);
        $this->assertArrayHasKey('status', $actual);
        $this->assertArrayHasKey('period_count', $actual);
        $this->assertArrayHasKey('period_unit', $actual);
        $this->assertArrayHasKey('is_infinite', $actual);
        $this->assertArrayHasKey('occurrences', $actual);
        $this->assertArrayHasKey('has_trial', $actual);
        $this->assertArrayHasKey('trial_occurrences', $actual);
        $this->assertArrayHasKey('initial_fee', $actual);
    }

    public function testPrepareMassaction()
    {
        $acl = $this->getModelMock('sheep_subscription/adminhtml_acl', array('canEditSubscriptionTypes'));
        $acl->expects($this->once())->method('canEditSubscriptionTypes')->willReturn(true);
        $this->replaceByMock('singleton', 'sheep_subscription/adminhtml_acl', $acl);

        $layoutMock = $this->getModelMock('core/layout', array('createBlock'));
        $layoutMock->expects($this->any())->method('createBlock')->will($this->returnValue($this->getBlockMock('core/text', array('toHtml'))));
        $massactionMock = $this->getBlockMock('adminhtml/widget_grid_massaction', array('toHtml', 'getLayout'));
        $massactionMock->expects($this->any())->method('getLayout')->will($this->returnValue($layoutMock));

        $block = $this->getBlockMock('sheep_subscription/adminhtml_type_grid', array('getMassactionBlock', 'getUrl'));
        $block->expects($this->any())->method('getMassactionBlock')->will($this->returnValue($massactionMock));

        EcomDev_Utils_Reflection::invokeRestrictedMethod($block, '_prepareMassaction');

        $this->assertEquals('ids', $massactionMock->getFormFieldName());
        $this->assertEquals(false, $massactionMock->getData('use_select_all'));
        $delete = $massactionMock->getItem('delete');
        $this->assertNotNull($delete);
        $this->assertEquals('Delete', $delete->getLabel());
    }


    public function testPrepareMassactionWithoutPrivileges()
    {
        $acl = $this->getModelMock('sheep_subscription/adminhtml_acl', array('canEditSubscriptionTypes'));
        $acl->expects($this->once())->method('canEditSubscriptionTypes')->willReturn(false);
        $this->replaceByMock('singleton', 'sheep_subscription/adminhtml_acl', $acl);

        $layoutMock = $this->getModelMock('core/layout', array('createBlock'));
        $layoutMock->expects($this->any())->method('createBlock')->will($this->returnValue($this->getBlockMock('core/text', array('toHtml'))));
        $massactionMock = $this->getBlockMock('adminhtml/widget_grid_massaction', array('toHtml', 'getLayout'));
        $massactionMock->expects($this->any())->method('getLayout')->will($this->returnValue($layoutMock));

        $block = $this->getBlockMock('sheep_subscription/adminhtml_type_grid', array('getMassactionBlock', 'getUrl'));
        $block->expects($this->any())->method('getMassactionBlock')->will($this->returnValue($massactionMock));

        EcomDev_Utils_Reflection::invokeRestrictedMethod($block, '_prepareMassaction');

        $delete = $massactionMock->getItem('delete');
        $this->assertNull($delete);
    }

}
