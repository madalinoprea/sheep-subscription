<?php

/**
 * Class Sheep_Subscription_Model_SalesRule_Observer
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2016, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Model_SalesRule_Observer
{

    /**
     * Observes salesrule_rule_condition_combine event and adds subscription related conditions to sales rules
     *
     * @param Varien_Event_Observer $observer
     */
    public function addSalesRulesConditions(Varien_Event_Observer $observer)
    {
        $helper = Mage::helper('sheep_subscription');

        /** @var Varien_Object $additional */
        $additional = $observer->getAdditional();

        $attributes = array();
        $attributes[] = array('label' => $helper->__('Subscriber Type'), 'value' => 'sheep_subscription/salesRule_condition_subscriber');
        $attributes[] = array('label' => $helper->__('Subscription Renewals'), 'value' => 'sheep_subscription/salesRule_condition_renewal');
        $attributes[] = array('label' => $helper->__('Has Subscriptions Of'), 'value' => 'sheep_subscription/salesRule_condition_product');

        $conditions = array();
        $conditions[] = array(
            'label' => 'Subscription Conditions',
            'value' => $attributes
        );

        $additional->setConditions($conditions);
    }
}
