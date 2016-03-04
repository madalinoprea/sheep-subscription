<?php

/**
 * Class Sheep_Subscription_Model_Notification_Cron
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Model_Notification_Cron
{

    public function log($message, $level = Zend_Log::INFO)
    {
        Mage::log(__CLASS__ . ': ' . $message, $level);
    }


    /**
     * Returns notification service
     *
     * @return Sheep_Subscription_Model_Notification_Service
     */
    public function getNotificationService()
    {
        return Mage::getSingleton('sheep_subscription/notification_service');
    }


    /**
     * Cron job that checks upcoming renewals and creates customer notification events
     */
    public function checkNextWeekUpcomingRenewals()
    {
        $helper = Mage::helper('sheep_subscription/renewal');
        if (!$helper->isUpcomingRenewalsNotificationEnabled()) {
            return 'disabled by config';
        }

        $startDate = Mage::getSingleton('core/date')->gmtDate();
        $endDate = $helper->getUpcomingRenewalDateThreshold($startDate);

        $this->log("Update notification events for upcoming renewals {$startDate} --- {$endDate} ...");

        $service = $this->getNotificationService();
        $notificationEventsCount = $service->addNotificationEventsForUpcomingRenewals($startDate, $endDate);

        $this->log("{$notificationEventsCount} notification events for upcoming renewals were added.");
        return "{$notificationEventsCount} notification events for upcoming renewals were added";
    }


    /**
     * Cron job that process subscription notifications events
     */
    public function processNotifications()
    {
        $this->getNotificationService()->processNotificationQueue();
    }


    /**
     * Cron job that adds notification events for all customers that have payment info about to expire attached to active or paused
     * subscriptions.
     */
    public function checkExpiredPaymentNotification()
    {
        /** @var Sheep_Subscription_Helper_Payment $helper */
        $helper = Mage::helper('sheep_subscription/payment');
        if (!$helper->isExpiredPaymentNotificationEnabled()) {
            return 'disabled by config';
        }

        $expirationDate = $helper->getExpirationDateThreshold();
        $this->log("Update notification events for expired payments before {$expirationDate} ...");

        $service = $this->getNotificationService();
        $count = $service->addNotificationEventsForExpiredPayments($expirationDate);

        $this->log("{$count} notification events for expired payment were added.");
        return "{$count} notification events for expired payments were added.";
    }
}
