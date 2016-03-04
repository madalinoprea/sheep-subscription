<?php

/**
 * Class Sheep_Subscription_Block_Subscription_Email_Overview
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Block_Subscription_Email_Overview extends Mage_Core_Block_Template
{
    protected $activeSubscriptions;
    protected $inactiveSubscriptions;
    protected $upcomingRenewals;
    protected $expiredPayments;
    protected $failingRenewals;


    /**
     * Returns customer specified as e-mail template
     *
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        return $this->getData('customer');
    }


    /**
     * Returns subscription view url
     *
     * @param $subscriptionId
     * @return string
     */
    public function getSubscriptionUrl($subscriptionId)
    {
        return Mage::helper('sheep_subscription')->getSubscriptionUrlInStore($subscriptionId, $this->getCustomer()->getStore()->getId());
    }


    /**
     * Returns upcoming renewals for current customer
     *
     * @return Sheep_Subscription_Model_Resource_Renewal_Collection
     */
    public function getUpcomingRenewals()
    {
        if ($this->upcomingRenewals === null) {
            $helper = Mage::helper('sheep_subscription/renewal');
            $upcomingRenewalDate = $helper->getUpcomingRenewalDateThreshold();

            /** @var Sheep_Subscription_Model_Resource_Renewal_Collection $renewals */
            $renewals = Mage::getModel('sheep_subscription/renewal')->getCollection();
            $renewals->addCustomerFilter($this->getCustomer()->getId());
            $renewals->addStatusFilter(array('in' => array(
                Sheep_Subscription_Model_Renewal::STATUS_PENDING,
                Sheep_Subscription_Model_Renewal::STATUS_PROCESSING,
                Sheep_Subscription_Model_Renewal::STATUS_WAITING
            )));
            $renewals->addFieldToFilter('date', array('to' => $upcomingRenewalDate, 'datetime' => true));
            $renewals->addOrder('date', Sheep_Subscription_Model_Resource_Renewal_Collection::SORT_ORDER_ASC);

            $this->upcomingRenewals = $renewals;
        }

        return $this->upcomingRenewals;
    }


    /**
     * Returns a list of renewals associated to the customer that were not processed and they failed at least once.
     *
     * @return Sheep_Subscription_Model_Resource_Renewal_Collection
     */
    public function getFailingRenewals()
    {
        if (!$this->failingRenewals) {
            $helper = Mage::helper('sheep_subscription/renewal');
            $renewals =  $helper->getCustomerRenewals($this->getCustomer()->getId());

            // A failing renewal is considered a renewal that is in processing and has
            $renewals->addStatusFilter(Sheep_Subscription_Model_Renewal::STATUS_PROCESSING);
            $renewals->addFieldToFilter('failed_payments_count', array('gt' => 0));

            $this->failingRenewals = $renewals;
        }

        return $this->failingRenewals;
    }


    /**
     * Returns active subscriptions
     *
     * @return Sheep_Subscription_Model_Resource_Subscription_Collection
     */
    public function getActiveSubscriptions()
    {
        if (!$this->activeSubscriptions) {
            $this->activeSubscriptions = Mage::helper('sheep_subscription/subscription')->getCustomerActiveSubscriptions($this->getCustomer()->getId());
            $this->activeSubscriptions->addNextRenewalDate();
        }

        return $this->activeSubscriptions;
    }


    /**
     * Returns inactive subscription
     *
     * @return Sheep_Subscription_Model_Resource_Subscription_Collection
     */
    public function getInactiveSubscriptions()
    {
        if (!$this->inactiveSubscriptions) {
            $this->inactiveSubscriptions = Mage::helper('sheep_subscription/subscription')->getCustomerSubscriptions($this->getCustomer()->getId());
            $this->inactiveSubscriptions->addStatusFilter(array('neq' => Sheep_Subscription_Model_Subscription::STATUS_ACTIVE));
        }

        return $this->inactiveSubscriptions;
    }


    /**
     * Returns subscription payment that are expired or about to expired assigned to active/paused suscription associated
     * to current customer.
     *
     * @return Sheep_Subscription_Model_Resource_Payment_Collection
     */
    public function getExpiredPayments()
    {
        if (!$this->expiredPayments) {
            $date = Mage::helper('sheep_subscription/payment')->getExpirationDateThreshold();

            /** @var Sheep_Subscription_Model_Notification_Service $notificationService */
            $notificationService = Mage::getModel('sheep_subscription/notification_service');
            $payments = $notificationService->getExpiredPayments($date);
            $payments->addFieldToFilter('customer_id', $this->getCustomer()->getId());

            $this->expiredPayments = $payments;
        }

        return $this->expiredPayments;
    }

}
