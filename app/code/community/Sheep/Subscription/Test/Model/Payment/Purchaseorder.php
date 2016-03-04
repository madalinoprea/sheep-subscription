<?php

/**
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_Model_Payment_Purchaseorder
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Model_Payment_Purchaseorder extends EcomDev_PHPUnit_Test_Case
{
    public function testConstruct()
    {
        $model = Mage::getModel('sheep_subscription/payment_purchaseorder');
        $this->assertNotNull($model);
        $this->assertInstanceOf('Sheep_Subscription_Model_Payment_Local', $model);
        $this->assertFalse($model->isGatewayManaged());
    }

}
