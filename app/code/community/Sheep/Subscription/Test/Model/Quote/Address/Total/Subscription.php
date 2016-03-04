<?php

/**
 * Class Sheep_Subscription_Test_Model_Quote_Address_Total_Subscription
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, {2015}, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_Model_Quote_Address_Total_Subscription
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Model_Quote_Address_Total_Subscription extends EcomDev_PHPUnit_Test_Case
{

    public function testCollectWithEmptyQuote()
    {
        $quoteMock = $this->getModelMock('sales/quote', array('setPssHasSubscriptions'));
        $quoteMock->expects($this->once())->method('setPssHasSubscriptions')->with(0);

        $addressMock = $this->getModelMock('sales/quote_address', array('getQuote'));
        $addressMock->expects($this->once())->method('getQuote')->willReturn($quoteMock);

        $object = $this->getModelMock('sheep_subscription/quote_address_total_subscription', array('_getAddressItems'));
        $object->expects($this->once())->method('_getAddressItems')->with($addressMock)->willReturn(array());

        $object->collect($addressMock);
    }

    public function testCollectWithQuoteWithoutSubscriptions() //
    {
        $quoteItem = $this->getModelMock('sales/quote_item', array('getProduct'));

        $helper = $this->getHelperMock('sheep_subscription/quote', array('isSubscriptionQuoteItem'));
        $helper->expects($this->once())->method('isSubscriptionQuoteItem')->with($quoteItem)->willReturn(false);
        $this->replaceByMock('helper', 'sheep_subscription/quote', $helper);

        $quoteMock = $this->getModelMock('sales/quote', array('setPssHasSubscriptions'));
        $quoteMock->expects($this->once())->method('setPssHasSubscriptions')->with(0);

        $addressMock = $this->getModelMock('sales/quote_address', array('getQuote', 'getAllNonNominalItems'));
        $addressMock->expects($this->once())->method('getQuote')->willReturn($quoteMock);
        $addressMock->expects($this->once())->method('getAllNonNominalItems')->willReturn(array(
            $quoteItem
        ));

        $object = Mage::getModel('sheep_subscription/quote_address_total_subscription');
        $object->collect($addressMock);
    }


    public function testCollectWithQuoteWithSubscriptions()
    {
        $quoteItem = $this->getModelMock('sales/quote_item', array('getProduct'));

        $helper = $this->getHelperMock('sheep_subscription/quote', array('isSubscriptionQuoteItem'));
        $helper->expects($this->once())->method('isSubscriptionQuoteItem')->with($quoteItem)->willReturn(true);
        $this->replaceByMock('helper', 'sheep_subscription/quote', $helper);

        $quoteMock = $this->getModelMock('sales/quote', array('setPssHasSubscriptions'));
        $quoteMock->expects($this->once())->method('setPssHasSubscriptions')->with(1);

        $addressMock = $this->getModelMock('sales/quote_address', array('getQuote', 'getAllNonNominalItems'));
        $addressMock->expects($this->once())->method('getQuote')->willReturn($quoteMock);
        $addressMock->expects($this->once())->method('getAllNonNominalItems')->willReturn(array(
            $quoteItem
        ));

        $object = Mage::getModel('sheep_subscription/quote_address_total_subscription');
        $object->collect($addressMock);
    }

    public function testCollectWithQuoteWithMixedProducts()
    {
        $quoteItem1 = $this->getModelMock('sales/quote_item', array('getProduct'));
        $quoteItem2 = $this->getModelMock('sales/quote_item');

        $helper = $this->getHelperMock('sheep_subscription/quote', array('isSubscriptionQuoteItem'));
        $helper->expects($this->at(0))->method('isSubscriptionQuoteItem')->with($quoteItem1)->willReturn(false);
        $helper->expects($this->at(1))->method('isSubscriptionQuoteItem')->with($quoteItem2)->willReturn(true);
        $this->replaceByMock('helper', 'sheep_subscription/quote', $helper);

        $quoteMock = $this->getModelMock('sales/quote', array('setPssHasSubscriptions'));
        $quoteMock->expects($this->once())->method('setPssHasSubscriptions')->with(1);

        $addressMock = $this->getModelMock('sales/quote_address', array('getQuote', 'getAllNonNominalItems'));
        $addressMock->expects($this->once())->method('getQuote')->willReturn($quoteMock);
        $addressMock->expects($this->once())->method('getAllNonNominalItems')->willReturn(array(
            $quoteItem1,
            $quoteItem2
        ));

        $object = Mage::getModel('sheep_subscription/quote_address_total_subscription');
        $object->collect($addressMock);
    }

}
