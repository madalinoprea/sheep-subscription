<?php
/**
 * Class Sheep_Subscription_Test_Model_System_Config_Source_Payment
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_Model_System_Config_Source_Payment
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Model_System_Config_Source_Payment extends EcomDev_PHPUnit_Test_Case
{
    /** @var Sheep_Subscription_Model_System_Config_Source_Payment $model */
    protected $model;


    protected function setUp()
    {
        $this->model = Mage::getModel('sheep_subscription/system_config_source_payment');
    }


    /**
     * @covers Sheep_Subscription_Model_System_Config_Source_Payment::toOptionArray
     */
    public function testToOptionArray()
    {
        $helperMock = $this->getHelperMock('sheep_subscription/payment', array('getActiveSubscriptionPaymentMethods'));
        $helperMock->expects($this->once())->method('getActiveSubscriptionPaymentMethods')->willReturn(array(
            new Varien_Object(array('code' => 'free', 'title' => 'Free')),
            new Varien_Object(array('code' => 'cards', 'title' => 'Credit Card'))
        ));
        $this->replaceByMock('helper', 'sheep_subscription/payment', $helperMock);

        $actual = $this->model->toOptionArray();
        $this->assertNotNull($actual);
        $this->assertCount(2, $actual);
        $this->assertEquals('cards', $actual[1]['value']);
        $this->assertEquals('Credit Card', $actual[1]['label']);
    }

}
