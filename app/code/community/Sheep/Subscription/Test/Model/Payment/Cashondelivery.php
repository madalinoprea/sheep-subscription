<?php

/**
 * Class Sheep_Subscription_Model_Payment_Cashondelivery
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_Model_Payment_Cashondelivery
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Model_Payment_Cashondelivery extends EcomDev_PHPUnit_Test_Case
{
    public function testType()
    {
        $model = Mage::getModel('sheep_subscription/payment_cashondelivery');
        $this->assertInstanceOf('Sheep_Subscription_Model_Payment_Local', $model);
        $this->assertFalse($model->isGatewayManaged());
    }

}
