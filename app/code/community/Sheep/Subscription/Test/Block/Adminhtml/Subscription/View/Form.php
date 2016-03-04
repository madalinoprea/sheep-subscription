<?php

/**
 * Class Sheep_Subscription_Test_Block_Adminhtml_Subscription_View_Form
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, {2015}, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_Block_Adminhtml_Subscription_View_Form
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Block_Adminhtml_Subscription_View_Form extends EcomDev_PHPUnit_Test_Case
{

    public function testConstruct()
    {
        $block = $this->getBlockMock('sheep_subscription/adminhtml_subscription_view_form', array('toHtml'));
        $this->assertEquals('sheep_subscription/subscription/view/form.phtml', $block->getTemplate());
    }
}
