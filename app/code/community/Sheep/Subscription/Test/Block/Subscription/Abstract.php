<?php

/**
 * Class Sheep_Subscription_Test_Block_Subscription_Abstract
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, {2015}, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_Block_Subscription_Abstract
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Block_Subscription_Abstract extends EcomDev_PHPUnit_Test_Case
{
    protected $block;

    protected function setUp()
    {
        parent::setUp();
        $this->block = $this->getBlockMock('sheep_subscription/subscription_abstract', array('toHtml'), true);
    }

    public function testSubscription()
    {
        $this->replaceRegistry('pss_subscription', 'subscription registry');
        $actual = $this->block->getSubscription();
        $this->assertEquals('subscription registry', $actual);
    }

}
