<?php

class Sheep_Subscription_Model_Notification_Observer
{
    /**
     * @return Sheep_Subscription_Model_Notification_Service
     */
    public function getService()
    {
        return Mage::getModel('sheep_subscription/notification_service');
    }


    /**
     * Listens to pss_renewal_create_order_after
     *
     * @param Varien_Event_Observer $observer
     */
    public function onRenewalCompleted(Varien_Event_Observer $observer)
    {
        $customerId = $observer->getEvent()->getRenewal()->getSubscription()->getCustomerId();

        $this->getService()->notifyCustomer($customerId);
    }


    /**
     * Listens to pss_pause_subscription, pss_resume_subscription, pss_cancel_subscription, pss_change_renewal_date, pss_expire_subscription events
     * and pss_renewal_error
     *
     * @param Varien_Event_Observer $observer
     */
    public function onSubscriptionUpdate(Varien_Event_Observer $observer)
    {
        /** @var Sheep_Subscription_Model_Subscription $subscription */
        $subscription = $observer->getSubscription();

        $this->getService()->notifyCustomer($subscription->getCustomerId());
    }

}
