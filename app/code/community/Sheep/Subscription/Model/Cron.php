<?php

/**
 * Class Sheep_Subscription_Model_Cron
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Model_Cron
{
    const RENEWAL_PROCESSING_QUEUE = 'renewal_processing_queue';
    // Hides messages for 2 hours
    const PROCESSING_MESSAGE_COUNT_PATH = 'sheep_subscription/renewals/process_iteration_messages';
    const DEFAULT_MESSAGE_COUNT = 10;
    const PROCESSING_MESSAGE_TIMEOUT = 'sheep_subscription/renewals/process_message_timeout';
    const PROCESSING_DEFAULT_MESSAGE_TIMEOUT = 7200;

    const INVENTORY_FORECAST_RECEIVER_PATH = 'sheep_subscription/reports/forecast_receiver';
    const INVENTORY_FORECAST_PERIOD_PATH = 'sheep_subscription/reports/forecast_period';

    /**
     * Class related logs. Current implementation add class name as message prefix.
     *
     * @param string $message
     * @param int    $level
     */
    public function log($message, $level = Zend_Log::INFO)
    {
        Mage::log(__CLASS__ . ': ' . $message, $level);
    }


    /**
     * Returns number of renewal messages that are processed per iteration
     *
     * @return int
     */
    public function getMessagesPerIteration()
    {
        $messageCount = (int)Mage::getStoreConfig(self::PROCESSING_MESSAGE_COUNT_PATH);
        if (!$messageCount) {
            $messageCount = self::DEFAULT_MESSAGE_COUNT;
        }

        return $messageCount;
    }


    /**
     * Returns number of seconds after which queue message can be re-processed
     *
     * @return int
     */
    public function getMessageTimeout()
    {
        $messageTimeout = (int)Mage::getStoreConfig(self::PROCESSING_MESSAGE_TIMEOUT);
        if (!$messageTimeout) {
            $messageTimeout = self::PROCESSING_DEFAULT_MESSAGE_TIMEOUT;
        }

        return $messageTimeout;
    }


    /**
     * @return Sheep_Subscription_Model_Service
     */
    public function getService()
    {
        return Mage::getModel('sheep_subscription/service');
    }


    /**
     * sheep_subscription_check_renewals cron job
     *
     * Selects pending renewals that needs to be processed, adds them to processing queue and moves them into PROCESSING
     * state.
     *
     * @return string
     */
    public function checkRenewals()
    {
        $now = Mage::getSingleton('core/date')->gmtDate();

        /** @var Mage_Core_Helper_Data $helper */
        $helper = Mage::helper('core');
        /** @var Sheep_Subscription_Model_Service $service */
        $service = $this->getService();
        $renewals = $service->getPendingRenewals($now);
        $renewalsScheduledCount = 0;
        $renewalsScheduleErrors = 0;
        $queue = Mage::helper('sheep_queue')->getQueue(self::RENEWAL_PROCESSING_QUEUE);

        /** @var Sheep_Subscription_Model_Renewal $renewal */
        foreach ($renewals as $renewal) {
            try {
                $messageData = array('renewal_id' => $renewal->getId(), 'date' => $renewal->getDate(), 'added_at' => $now);
                $message = $helper->jsonEncode($messageData);
                if (!$message) {
                    throw new Exception('Unable to encode message');
                }

                $queue->send($message);
                $renewal->setStatus(Sheep_Subscription_Model_Renewal::STATUS_PROCESSING);
                $renewal->save();

                $renewalsScheduledCount++;
            } catch (Exception $e) {
                Mage::log("Unable to schedule processing for subscriptionId={$renewal->getSubscriptionId()} renewalId={$renewal->getId()}: " . $e->getMessage());
                $renewalsScheduleErrors++;
            }
        }

        return "{$renewalsScheduledCount} renewals moved to processing, {$renewalsScheduleErrors} renewals had errors";
    }


    /**
     * Consumes renewal processing queue (create order for renewals, adds new renewals)
     *
     * @throws Zend_Queue_Exception
     */
    public function processRenewals()
    {
        $queue = Mage::helper('sheep_queue')->getQueue(self::RENEWAL_PROCESSING_QUEUE);
        /** @var Sheep_Subscription_Model_Service $service */
        $service = $this->getService();
        $messages = $queue->receive($this->getMessagesPerIteration(), $this->getMessageTimeout());
        $helper = Mage::helper('core');
        $processedMessageCount = 0;
        $createdOrdersCount = 0;

        foreach ($messages as $message) {
            $processedMessageCount++;

            try {
                $messageBody = $helper->jsonDecode($message->body);
                if (!$messageBody) {
                    Mage::log("Unable to parse messageId={$message->message_id}");
                    continue;
                }

                /** @var Sheep_Subscription_Model_Renewal $renewal */
                $renewal = Mage::getModel('sheep_subscription/renewal')->load($messageBody['renewal_id']);
                if (!$renewal->getId()) {
                    Mage::log("Unable to load messageId={$message->message_id} renewalId={$messageBody['renewal_id']}");
                    continue;
                }

                if ($renewal->getStatus() != Sheep_Subscription_Model_Renewal::STATUS_PROCESSING) {
                    Mage::log("Unable to process messageId={$message->message_id} renewalId={$messageBody['renewal_id']}: invalid status {$renewal->getStatusLabel()}");
                    $queue->deleteMessage($message);
                    continue;
                }

                // Delete message if renewal was payed
                if ($service->processRenewal($renewal)) {
                    $createdOrdersCount++;
                    $queue->deleteMessage($message);
                }

            } catch (Exception $e) {
                Mage::log("Unable to process messageId={$message->message_id}: " . $e->getMessage());
            }
        }

        return "{$createdOrdersCount} created orders, {$processedMessageCount} processed messages";
    }


    /**
     * Returns number of days included in inventory forecast
     *
     * @return int
     */
    public function getInventoryForecastDays()
    {
        $config = (int) Mage::getStoreConfig(self::INVENTORY_FORECAST_PERIOD_PATH);
        return $config ?: 90;
    }


    /**
     * Inventory forecast report cron job
     */
    public function sendInventoryForecastReport()
    {
        $toEmail = Mage::getStoreConfig(self::INVENTORY_FORECAST_RECEIVER_PATH);

        if (!$toEmail) {
            return;
        }

        $helper = Mage::helper('sheep_util/email');

        // Generate report forecast for next period
        $fromDate = new Zend_Date;
        $toDate = new Zend_Date;
        $toDate->addDay($this->getInventoryForecastDays());

        // Prepare report
        /** @var Sheep_Subscription_Block_Adminhtml_Report_Inventory_Planning_Grid $reportBlock */
        $reportBlock = Mage::app()->getLayout()->createBlock('sheep_subscription/adminhtml_report_inventory_planning_grid');

        $reportBlock->setFilter('report_from', Mage::helper('core')->formatDate($fromDate));
        $reportBlock->setFilter('report_to', Mage::helper('core')->formatDate($toDate));
        $reportBlock->setFilter('report_period', 'month');

        // Export report data
        $subject = 'Inventory Forecast ' . $fromDate->toString(Zend_Date::DATE_SHORT) . ' - ' . $toDate->toString(Zend_Date::DATE_SHORT);
        $exportData = $reportBlock->getCsv();

        // Make sure export directory is created
        $exportPath = Mage::getBaseDir('var') . DS . 'subscription_export';
        $io = new Varien_Io_File();
        $io->mkdir($exportPath);

        $exportFilepath = $exportPath . DS . 'inventory_forecast_' . date('Y-m-d') . '.csv';
        $io->write($exportFilepath, $exportData);

        // Prepare e-mail attributes
        $body = 'This e-mail contain inventory forecast as attachment.';

        $this->log($subject . ' generated to ' . $exportFilepath);
        $helper->sendEmail('Inventory Forecast', $toEmail, $subject, $body, array($exportFilepath));
        $this->log($subject . ' e-mailed to ' . $toEmail);
    }

}
