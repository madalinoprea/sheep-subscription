<?php

/**
 * Class Sheep_Subscription_Test_Helper_Payment
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, {2015}, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_Helper_Payment
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Helper_Payment extends EcomDev_PHPUnit_Test_Case
{

    public function testGetSubscriptionPaymentMethodModel()
    {
        $helper = $this->getHelperMock('sheep_subscription/payment', array('getActivePaymentMethods'));
        $helper->expects($this->once())->method('getActivePaymentMethods')
            ->willReturn(
                array(
                    'checkmo'        => $this->getModelMock('payment/method_checkmo'),
                    'cashondelivery' => $this->getModelMock('payment/method_cashondelivery')
                ));

        $subscriptionPayment = $helper->getSubscriptionPaymentMethodModel('checkmo');
        $this->assertNotNull($subscriptionPayment);
        $this->assertInstanceOf('Sheep_Subscription_Model_Payment_Interface', $subscriptionPayment);
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Payment method cashondelivery is not active
     */
    public function testGetSubscriptionPaymentMethodModelWithDisabledMethod()
    {
        $helper = $this->getHelperMock('sheep_subscription/payment', array('getActivePaymentMethods'));
        $helper->expects($this->once())->method('getActivePaymentMethods')
            ->willReturn(
                array(
                    'checkmo' => $this->getModelMock('payment/method_checkmo'),
                ));

        $helper->getSubscriptionPaymentMethodModel('cashondelivery');
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Payment method esotericpayment is not a subscription payment
     */
    public function testGetSubscriptionPaymentMethodModelWithoutImplemention()
    {
        $helper = $this->getHelperMock('sheep_subscription/payment', array('getActivePaymentMethods'));
        $helper->expects($this->once())->method('getActivePaymentMethods')
            ->willReturn(
                array(
                    'esotericpayment' => $this->getModelMock('payment/method_checkmo'),
                ));

        $helper->getSubscriptionPaymentMethodModel('esotericpayment');
    }


    public function testGetSubscriptionPaymentMethodCodes()
    {
        $helper = $this->getHelperMock('sheep_subscription/payment', array('getActivePaymentMethods'));
        $actual = $helper->getSubscriptionPaymentMethodCodes();

        $this->assertNotNull($actual);
        $this->assertContains('checkmo', $actual);
        $this->assertContains('cashondelivery', $actual);
    }


    /**
     * Tests that retrieval of active payment methods is delegated to payment/config helper
     */
    public function testGetActivePaymentMethods()
    {
        $paymentHelperMock = $this->getModelMock('payment/config', array('getActiveMethods'));
        $paymentHelperMock->expects($this->once())->method('getActiveMethods');
        $this->replaceByMock('singleton', 'payment/config', $paymentHelperMock);

        $helper = $this->getHelperMock('sheep_subscription/payment', array('getAllowedSubscriptionPaymentMethodCodes'));
        $helper->getActivePaymentMethods();
    }


    public function testGetActiveSubscriptionPaymentMethods()
    {
        $helper = $this->getHelperMock('sheep_subscription/payment', array('getActivePaymentMethods', 'getSubscriptionPaymentMethodCodes'));
        $helper->expects($this->once())->method('getActivePaymentMethods')
            ->willReturn(
                array(
                    'checkmo'        => $this->getModelMock('payment/method_checkmo'),
                    'cashondelivery' => $this->getModelMock('payment/method_cashondelivery')
                ));
        $helper->expects($this->once())->method('getSubscriptionPaymentMethodCodes')->willReturn(array('checkmo'));


        $actual =  $helper->getActiveSubscriptionPaymentMethods();
        $this->assertNotEmpty($actual);
        $this->assertCount(1, $actual);
        $this->assertNotNull($actual[0]);
        $this->assertInstanceOf('Sheep_Subscription_Model_Payment_Checkmo', $actual[0]);
    }


    public function testGetAllowedSubscriptionPaymentMethodCodes()
    {
        $helper = $this->getHelperMock('sheep_subscription/payment', array('getActiveSubscriptionPaymentMethods'));
        $actual = $helper->getAllowedSubscriptionPaymentMethodCodes();

        $this->assertNotNull($actual);
        $this->assertTrue(is_array($actual));
    }


    public function testIsSubscriptionPayment()
    {
        $helper = $this->getHelperMock('sheep_subscription/payment', array('getAllowedSubscriptionPaymentMethodCodes'));
        $helper->expects($this->any())->method('getAllowedSubscriptionPaymentMethodCodes')
            ->willReturn(array('checkmo', 'cashondelivery'));


        $this->assertTrue($helper->isSubscriptionPayment('checkmo'));
        $this->assertTrue($helper->isSubscriptionPayment('cashondelivery'));
        $this->assertFalse($helper->isSubscriptionPayment('not_found'));
    }


    public function testGetExpirationDateThreshold()
    {
        $helper = $this->getHelperMock('sheep_subscription/payment', array('getDaysBeforeExpiredPaymentNotification'));
        $helper->expects($this->any())->method('getDaysBeforeExpiredPaymentNotification')->willReturn(7);

        $actual = $helper->getExpirationDateThreshold('2016-02-23');
        $this->assertEquals('2016-03-01', $actual);
    }
}
