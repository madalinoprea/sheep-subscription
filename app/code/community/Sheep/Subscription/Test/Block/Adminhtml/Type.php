<?php

/**
 * Class Sheep_Subscription_Test_Block_Adminhtml_Type
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_Block_Adminhtml_Type
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Block_Adminhtml_Type extends EcomDev_PHPUnit_Test_Case
{
    /** @var Sheep_Subscription_Block_Adminhtml_Type $block */
    protected $block;

    protected function setUp()
    {
        $acl = $this->getModelMock('sheep_subscription/adminhtml_acl', array('canEditSubscriptionTypes'));
        $acl->expects($this->any())->method('canEditSubscriptionTypes')->willReturn(true);
        $this->replaceByMock('singleton', 'sheep_subscription/adminhtml_acl', $acl);

        $this->block = $this->getBlockMock('sheep_subscription/adminhtml_type', array('getUrl'));
    }

    public function testConstruct()
    {
        $this->assertEquals('sheep_subscription', EcomDev_Utils_Reflection::getRestrictedPropertyValue($this->block, '_blockGroup'));
        $this->assertEquals('adminhtml_type', EcomDev_Utils_Reflection::getRestrictedPropertyValue($this->block, '_controller'));
        $this->assertEquals('Subscription Types', EcomDev_Utils_Reflection::getRestrictedPropertyValue($this->block, '_headerText'));
        $this->assertEquals('Add Subscription Type', EcomDev_Utils_Reflection::getRestrictedPropertyValue($this->block, '_addButtonLabel'));
    }
}
