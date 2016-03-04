<?php

/**
 * Class Sheep_Subscription_Model_SalesRule_ObserverTest
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2016, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_Model_SalesRule_Observer
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Model_SalesRule_Observer extends EcomDev_PHPUnit_Test_Case
{
    /** @var Sheep_Subscription_Model_SalesRule_Observer $model */
    protected $model;


    protected function setUp()
    {
        $this->model = Mage::getModel('sheep_subscription/salesRule_observer');
    }


    public function testAddSalesRulesConditions()
    {
        $additional = $this->getMock('Varien_Object', array('setConditions'));
        $additional->expects($this->once())->method('setConditions')->with(array(
            array(
                'label' => 'Subscription Conditions',
                'value' => array(
                    array('label' => 'Subscriber Type', 'value' => 'sheep_subscription/salesRule_condition_subscriber'),
                    array('label' => 'Subscription Renewals', 'value' => 'sheep_subscription/salesRule_condition_renewal'),
                    array('label' => 'Has Subscriptions Of', 'value' => 'sheep_subscription/salesRule_condition_product'),
                )
            )
        ));

        $observer = new Varien_Event_Observer(array('additional' => $additional));

        $this->model->addSalesRulesConditions($observer);
    }

}
