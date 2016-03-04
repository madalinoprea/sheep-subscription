<?php

/**
 * Class Sheep_Subscription_Helper_Renewal
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Helper_Renewal extends Mage_Core_Helper_Abstract
{
    const MAX_FAILED_PAYMENTS_PATH = 'sheep_subscription/renewals/max_failed_payments';
    const UPCOMING_RENEWALS_NOTIFICATION_PATH = 'sheep_subscription/renewals/upcoming_renewals_notification';
    const DAYS_BEFORE_RENEWAL_PATH = 'sheep_subscription/renewals/days_before_renewal';

    /**
     * Returns subscription renewal status options
     *
     * @return array
     */
    public function getStatusOptions()
    {
        return array(
            Sheep_Subscription_Model_Renewal::STATUS_PENDING    => $this->__('Pending'),
            Sheep_Subscription_Model_Renewal::STATUS_PROCESSING => $this->__('Processing'),
            Sheep_Subscription_Model_Renewal::STATUS_WAITING    => $this->__('Waiting'),
            Sheep_Subscription_Model_Renewal::STATUS_PAYED      => $this->__('Payed'),
            Sheep_Subscription_Model_Renewal::STATUS_FAILED     => $this->__('Failed')
        );
    }

    /**
     * Returns pending renewal for subscription that was last time paid on specified date
     *
     * @param Sheep_Subscription_Model_Subscription $subscription
     * @param                                       $lastPaidDate
     * @return Sheep_Subscription_Model_Renewal
     * @throws Exception
     */
    public function getRenewal(Sheep_Subscription_Model_Subscription $subscription, $lastPaidDate)
    {
        $renewalDate = $subscription->getType()->getNextRenewalDate($lastPaidDate);

        /** @var Sheep_Subscription_Model_Renewal $renewal */
        $renewal = Mage::getModel('sheep_subscription/renewal');
        $renewal->setSubscriptionId($subscription->getId());
        $renewal->setStatus(Sheep_Subscription_Model_Renewal::STATUS_PENDING);
        $renewal->setDate($renewalDate);

        return $renewal;
    }


    /**
     * Returns all renewals assigned on subscriptions belonging to specified customer
     *
     * @param $customerId
     * @return Sheep_Subscription_Model_Resource_Renewal_Collection
     */
    public function getCustomerRenewals($customerId)
    {
        /** @var Sheep_Subscription_Model_Resource_Renewal_Collection $renewals */
        $renewals = Mage::getModel('sheep_subscription/renewal')->getCollection();
        $renewals->addCustomerFilter($customerId);

        return $renewals;
    }


    /**
     * Returns accepted number of failed payments
     *
     * @param null $store
     * @return int
     */
    public function getMaxFailedPayments($store = null)
    {
        return (int)Mage::getStoreConfig(self::MAX_FAILED_PAYMENTS_PATH, $store);
    }


    /**
     * Checks if notifications should be sent for upcoming renewals
     *
     * @param null $store
     * @return bool
     */
    public function isUpcomingRenewalsNotificationEnabled($store = null)
    {
        return (boolean)Mage::getStoreConfig(self::UPCOMING_RENEWALS_NOTIFICATION_PATH, $store);
    }


    /**
     * Returns number of days before renewal when notification is sent.
     *
     * @param null $store
     * @return int
     */
    public function getDaysBeforeRenewal($store = null)
    {
        $config = (int) Mage::getStoreConfig(self::DAYS_BEFORE_RENEWAL_PATH, $store);
        return $config ?: 7;
    }


    /**
     * Returns date that needs to be checked based on specified current date to identify which renewals
     * are upcoming.
     *
     * @param string|null $currentDate
     * @return string
     */
    public function getUpcomingRenewalDateThreshold($currentDate = null)
    {
        $currentDate = $currentDate ?: Mage::getSingleton('core/date')->gmtDate();
        $days = $this->getDaysBeforeRenewal();

        $expirationDate = date('Y-m-d H:i:s', strtotime("+{$days} days {$currentDate}"));

        return $expirationDate;
    }

}
