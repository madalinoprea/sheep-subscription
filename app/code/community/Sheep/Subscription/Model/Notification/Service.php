<?php

/**
 * Class Sheep_Subscription_Model_Notification_Service defines business logic about how customers gets notified about activity related to their subscriptions.
 *
 * Currently, we have these activities:
 * - Every week, cron sheep_subscription_notification_upcoming_renewals will run and it will add notification
 * events for customers that have upcoming renewals during next week
 * - Every time a renewal gets completed, we add a notification event
 * - Every hour, we process notification events and we send a subscription overview e-mail
 *
 * A timestamp is attached to each notification event and it is used to verify if an e-mail was already sent after
 * event was created.
 *
 * TODO: Send notifications when a renewal have failed or subscription changed its status
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Model_Notification_Service
{
    const NOTIFICATION_QUEUE = 'ss_notifications';
    const NEW_SUBSCRIPTION_EMAIL_TEMPLATE_ID = 'sheep_subscription_new_subscription';
    const SUBSCRIPTION_OVERVIEW_EMAIL_TEMPLATE_ID = 'sheep_subscription_overview';


    public function log($message, $level = Zend_Log::INFO)
    {
        Mage::log(__CLASS__ . ': ' . $message, $level);
    }


    /**
     * Returns notification events queue
     *
     * @return Zend_Queue
     */
    public function getNotificationQueue()
    {
        return Mage::helper('sheep_queue')->getQueue(self::NOTIFICATION_QUEUE);
    }


    /**
     * @param $startDate
     * @param $endDate
     * @return Sheep_Subscription_Model_Resource_Renewal_Collection
     */
    public function getUpcomingRenewals($startDate, $endDate)
    {
        /** @var Sheep_Subscription_Model_Resource_Renewal_Collection $renewals */
        $renewals = Mage::getModel('sheep_subscription/renewal')->getCollection();
        $renewals->addStatusFilter(Sheep_Subscription_Model_Renewal::STATUS_PENDING);
        $renewals->addBetweenFilter($startDate, $endDate);

        return $renewals;
    }


    /**
     * Returns subscription customer profile associated to specified customer
     *
     * @param int $customerId
     * @return Sheep_Subscription_Model_Profile
     */
    public function getCustomerProfile($customerId)
    {
        return Mage::getModel('sheep_subscription/profile')->load($customerId);
    }


    /**
     * Adds a notification event for all customers that have pending (upcoming) renewals in specified period.
     *
     * @param $startDate
     * @param $endDate
     * @return int
     */
    public function addNotificationEventsForUpcomingRenewals($startDate, $endDate)
    {
        $renewals = $this->getUpcomingRenewals($startDate, $endDate);
        $renewals->addSubscriptionData(array('customer_id'));
        $renewals->getSelect()->group('subscription.customer_id');

        foreach ($renewals as $renewal) {
            $this->notifyCustomer($renewal->getData('customer_id'));
        }

        return count($renewals);
    }


    /**
     * Returns subscription payments that will expire before specified date and they are attached to
     * active subscriptions.
     *
     * @param string $date
     * @return Sheep_Subscription_Model_Resource_Payment_Collection
     */
    public function getExpiredPayments($date)
    {
        /** @var Sheep_Subscription_Model_Resource_Payment_Collection $subscriptionPayments */
        $subscriptionPayments = Mage::getResourceModel('sheep_subscription/payment_collection');
        $subscriptionPayments->addEarlierFilter($date);
        $subscriptionPayments->addSubscriptionData(array('status', 'customer_id'));
        $subscriptionPayments->addFieldToFilter('status', Sheep_Subscription_Model_Subscription::STATUS_ACTIVE);

        return $subscriptionPayments;
    }


    /**
     * Adds a notification event for customers that have active subscriptions with payment information
     * that has expiration date before specified date.
     *
     * Returns number of customer added to notification queue.
     *
     * @param string $date Date string using format Y-m-d
     * @return int
     */
    public function addNotificationEventsForExpiredPayments($date)
    {
        $payments = $this->getExpiredPayments($date);
        $payments->getSelect()->group('customer_id');

        $paymentsData = $payments->getData();

        // Add notification event for every customer
        foreach ($paymentsData as $paymentInfo) {
            $this->notifyCustomer($paymentInfo['customer_id']);
        }

        return count($paymentsData);
    }


    /**
     * Adds a event that specified customer needs to be notified (will receive an overview of his current subscriptions)
     *
     * @param int $customerId
     */
    public function notifyCustomer($customerId)
    {
        if (!$customerId) {
            return;
        }

        $eventData = array(
            'customer_id' => $customerId,
            'added_at'    => Mage::getSingleton('core/date')->gmtDate()
        );
        $message = Mage::helper('core')->jsonEncode($eventData);

        $this->getNotificationQueue()->send($message);
    }


    /**
     * @param int $messageCount
     * @param int $timeout
     * @throws Zend_Queue_Exception
     */
    public function processNotificationQueue($messageCount = 50, $timeout = 7200)
    {
        $queue = $this->getNotificationQueue();
        $messages = $queue->receive($messageCount, $timeout);

        /** @var Zend_Queue_Message $message */
        foreach ($messages as $message) {
            try {
                $eventData = Mage::helper('core')->jsonDecode($message->body);
                $this->processNotificationEvent($eventData);
                $queue->deleteMessage($message);
            } catch (Exception $e) {
                $this->log("Unable to process messageId={$message->message_id}: " . $e->getMessage());
            }
        }
    }


    /**
     * Process events added by @see ::notifyCustomer method
     *
     * @param $eventData
     * @throws Exception
     */
    public function processNotificationEvent($eventData)
    {
        $customerId = $eventData['customer_id'];
        $customerProfile = $this->getCustomerProfile($customerId);
        $notifiedAt = $customerProfile->getNotifiedAt();

        // was this event added before last notification sent to this customer
        if ($notifiedAt && strtotime($eventData['added_at']) < strtotime($notifiedAt)) {
            return;
        }

        // Send notification
        if (!$this->sendCustomerOverviewEmail($customerId)) {
            throw new Exception('Unable to sent customer overview e-mail');
        }

        //  Update notified at
        $customerProfile->setCustomerId($eventData['customer_id']);
        $customerProfile->setNotifiedAt(Mage::getSingleton('core/date')->gmtDate());
        $customerProfile->save();
    }


    /**
     * Sends an overview of customer subscriptions
     *
     * @param $customerId
     */
    public function sendCustomerOverviewEmail($customerId)
    {
        /** @var Mage_Customer_Model_Customer $customer */
        $customer = Mage::getModel('customer/customer')->load($customerId);


        $toEmail = $customer->getEmail();
        $toName = $customer->getName();
        $storeId = $customer->getStore()->getId();
        $vars = array(
            'customer'              => $customer,
            'subscription_list_url' => Mage::helper('sheep_subscription')->getSubscriptionListUrl()
        );
        $templateId = self::SUBSCRIPTION_OVERVIEW_EMAIL_TEMPLATE_ID;
        $sender = 'sales';

        /** @var Mage_Core_Model_Email_Template $emailTemplate */
        $emailTemplate = Mage::getModel('core/email_template');
        $emailTemplate->sendTransactional(
            $templateId,
            $sender,
            $toEmail,
            $toName,
            $vars,
            $storeId
        );

        return $emailTemplate->getSentSuccess();
    }


    /**
     * @param Sheep_Subscription_Model_Subscription $subscription
     */
    public function sendNewSubscriptionEmail(Sheep_Subscription_Model_Subscription $subscription)
    {
        $this->sendSubscriptionEmail(self::NEW_SUBSCRIPTION_EMAIL_TEMPLATE_ID, $subscription);
    }


    /**
     * Sends a subscription e-mail
     *
     * @param string                                $templateId
     * @param Sheep_Subscription_Model_Subscription $subscription
     * @return bool    True on success
     * @throws Mage_Core_Exception
     */
    public function sendSubscriptionEmail($templateId, Sheep_Subscription_Model_Subscription $subscription)
    {
        $toEmail = $subscription->getCustomer()->getEmail();
        $toName = $subscription->getCustomer()->getName();
        $storeId = $subscription->getQuote()->getStoreId();
        $vars = $this->_getEmailTemplateVariables($subscription);
        $sender = 'sales';

        /** @var Mage_Core_Model_Email_Template $emailTemplate */
        $emailTemplate = Mage::getModel('core/email_template');
        $emailTemplate->sendTransactional(
            $templateId,
            $sender,
            $toEmail,
            $toName,
            $vars,
            $storeId
        );

        return $emailTemplate->getSentSuccess();
    }


    /**
     * Returns required variables for subscription e-mail template
     *
     * @param Sheep_Subscription_Model_Subscription $subscription
     * @return array
     */
    protected function _getEmailTemplateVariables(Sheep_Subscription_Model_Subscription $subscription)
    {
        $storeId = $subscription->getQuote()->getStoreId();
        $vars = array(
            'subscription'         => $subscription,
            'subscription_url'     => Mage::helper('sheep_subscription')->getSubscriptionUrlInStore($subscription->getId(), $subscription->getQuote()->getStoreId()),
            'payment_html'         => '',
            'has_shipping'         => !$subscription->getIsVirtual(),
            'shipping_description' => $subscription->getIsVirtual() ? '' : $subscription->getShippingAddress()->getShippingDescription(),
        );

        try {
            $paymentBlock = Mage::helper('payment')->getInfoBlock($subscription->getPayment())->setIsSecureMode(true);
            $paymentBlock->getMethod()->setStore($storeId);
            $vars['payment_html'] = $paymentBlock->toHtml();

        } catch (Exception $e) {
            Mage::log('Unable to build payment html subscriptionId=' . $subscription->getId());
        }

        return $vars;
    }

}
