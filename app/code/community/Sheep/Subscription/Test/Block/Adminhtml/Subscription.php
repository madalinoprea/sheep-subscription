<?php

/**
 * Class Sheep_Subscription_Test_Block_Adminhtml_Subscription
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, {2015}, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_Block_Adminhtml_Subscription
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Block_Adminhtml_Subscription extends EcomDev_PHPUnit_Test_Case
{
    protected $block;

    protected function setUp()
    {
        parent::setUp();
        $this->block = $this->getBlockMock('sheep_subscription/adminhtml_subscription', array('toHtml', 'getUrl'));
    }

    public function testConstruct()
    {
        $this->assertEquals('sheep_subscription', EcomDev_Utils_Reflection::getRestrictedPropertyValue($this->block, '_blockGroup'));
        $this->assertEquals('adminhtml_subscription', EcomDev_Utils_Reflection::getRestrictedPropertyValue($this->block, '_controller'));
        $this->assertEquals('Purchased Subscriptions', EcomDev_Utils_Reflection::getRestrictedPropertyValue($this->block, '_headerText'));

        $buttons = EcomDev_Utils_Reflection::getRestrictedPropertyValue($this->block, '_buttons');
        $this->assertArrayNotHasKey('add', $buttons);
    }

}
