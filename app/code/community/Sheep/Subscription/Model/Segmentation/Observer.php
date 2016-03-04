<?php

/**
 * Class Sheep_Subscription_Model_Segmentation_Observer
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Model_Segmentation_Observer
{

    /**
     * @return Sheep_Subscription_Model_Segmentation_Service
     */
    public function getService()
    {
        return Mage::getSingleton('sheep_subscription/segmentation_service');

    }


    /**
     * Observes pss_create_subscription and pss_resume_subscription and tries to promote subscription's customer
     *
     * @param Varien_Event_Observer $observer
     */
    public function onActivateSubscription(Varien_Event_Observer $observer)
    {
        /** @var Sheep_Subscription_Model_Subscription $subscription */
        $subscription = $observer->getSubscription();

        if ($subscription && $subscription->getCustomerId()) {
            $this->getService()->promoteCustomer($subscription->getCustomer());
        }
    }


    /**
     * Observes pss_pause_subscription, pss_cancel_subscription and pss_expire_subscription events and tries to demote
     * subscription customer
     *
     * @param Varien_Event_Observer $observer
     */
    public function onDeactivateSubscription(Varien_Event_Observer $observer)
    {
        /** @var Sheep_Subscription_Model_Subscription $subscription */
        $subscription = $observer->getSubscription();

        if ($subscription && $subscription->getCustomerId()) {
            $this->getService()->demoteCustomer($subscription->getCustomer());
        }
    }

}
