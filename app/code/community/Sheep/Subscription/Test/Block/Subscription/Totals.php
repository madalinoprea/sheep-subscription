<?php

/**
 * Class Sheep_Subscription_Test_Block_Subscription_Totals
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, {2015}, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_Block_Subscription_Totals
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Block_Subscription_Totals extends EcomDev_PHPUnit_Test_Case
{
    public function testGetSubscription()
    {
        $block = $this->getBlockMock('sheep_subscription/subscription_totals', array('toHtml'));
        $this->replaceRegistry('pss_subscription', 'subscription registry');
        $actual = $block->getSubscription();
        $this->assertEquals('subscription registry', $actual);
    }


    /**
     * Subscription can be also assigned from e-mail templates
     */
    public function testGetSubscriptionAssigned()
    {
        $block = $this->getBlockMock('sheep_subscription/subscription_totals', array('toHtml'));
        $this->replaceRegistry('pss_subscription', NULL);
        $block->setData('subscription', 'assigned subscription');
        $actual = $block->getSubscription();
        $this->assertEquals('assigned subscription', $actual);
    }


    public function testGetQuote()
    {
        $subscriptionMock = $this->getModelMock('sheep_subscription/subscription', array('getQuote'));
        $subscriptionMock->expects($this->once())->method('getQuote');

        $block = $this->getBlockMock('sheep_subscription/subscription_totals', array('getSubscription'));
        $block->expects($this->once())->method('getSubscription')->willReturn($subscriptionMock);

        $block->getQuote();
    }


    public function testFormatValue()
    {
        $storeMock = $this->getModelMock('core/store', array('getCurrentCurrencyCode'));
        $storeMock->expects($this->once())->method('getCurrentCurrencyCode')->willReturn('EUR');

        $quoteMock = $this->getModelMock('sales/quote', array('getStore'));
        $quoteMock->expects($this->once())->method('getStore')->willReturn($storeMock);

        $block = $this->getBlockMock('sheep_subscription/subscription_totals', array('getQuote'));
        $block->expects($this->once())->method('getQuote')->willReturn($quoteMock);

        $total = new Varien_Object(array('is_formated' => true, 'value' => '200'));
        $actual = $block->formatValue($total);
        $this->assertEquals('200', $actual);

        // test formatted value
        $total = new Varien_Object(array('is_formated' => false, 'value' => '300'));
        $actual = $block->formatValue($total);
        $this->assertEquals('<span class="price">€300.00</span>', $actual);
    }
}

