<?php

/**
 * Class Sheep_Subscription_Helper_Payment
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Helper_Payment extends Mage_Core_Helper_Abstract
{
    const ALLOWED_SUBSCRIPTION_PAYMENTS_PATH = 'sheep_subscription/checkout/payment_methods';
    const EXPIRED_PAYMENT_NOTIFICATION_PATH = 'sheep_subscription/renewals/expired_payment_notification';
    const DAYS_BEFORE_EXPIRED_PAYMENT_NOTIFICATION_PATH = 'sheep_subscription/renewals/expired_payment_days_before';


    /**
     * Returns subscription payment method for specified payment method code
     *
     * @param string $paymentMethodCode
     * @return Sheep_Subscription_Model_Payment_Abstract
     * @throws Exception
     */
    public function getSubscriptionPaymentMethodModel($paymentMethodCode)
    {
        $activePaymentMethods = $this->getActivePaymentMethods();
        if (!array_key_exists($paymentMethodCode, $activePaymentMethods)) {
            throw new Exception("Payment method {$paymentMethodCode} is not active.");
        }

        $subscriptionPaymentModelUri = "sheep_subscription/payment_{$paymentMethodCode}";
        /** @var Sheep_Subscription_Model_Payment_Interface $subscriptionPaymentModel */
        $subscriptionPaymentModel = Mage::getModel($subscriptionPaymentModelUri);
        if (!$subscriptionPaymentModel) {
            throw new Exception("Payment method {$paymentMethodCode} is not a subscription payment.");
        }
        $subscriptionPaymentModel->setPayment($activePaymentMethods[$paymentMethodCode]);

        return $subscriptionPaymentModel;
    }


    /**
     * Return codes of payment methods that are expected to have a subscription implementation
     *
     * @return string[]
     */
    public function getSubscriptionPaymentMethodCodes()
    {
        $paymentMethodCodes = Mage::getConfig()->getNode('sheep_subscription/payment_methods')->asArray();
        return array_keys($paymentMethodCodes);
    }


    /**
     * Returns Magento active payment methods
     *
     * @return Mage_Payment_Model_Method_Abstract[]
     */
    public function getActivePaymentMethods()
    {
        return Mage::getSingleton('payment/config')->getActiveMethods();
    }


    /**
     * Returns subscription payment methods for payments that are active and have a subscription implementation
     *
     * @return Sheep_Subscription_Model_Payment_Interface[]
     */
    public function getActiveSubscriptionPaymentMethods()
    {
        $subscriptionPaymentMethods = array();
        $subscriptionPaymentMethodCodes = $this->getSubscriptionPaymentMethodCodes();

        foreach ($subscriptionPaymentMethodCodes as $paymentMethodCode) {
            try {
                $subscriptionPaymentModel = $this->getSubscriptionPaymentMethodModel($paymentMethodCode);
                $subscriptionPaymentMethods[] = $subscriptionPaymentModel;
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }

        return $subscriptionPaymentMethods;
    }


    /**
     * Returns codes for payment methods that can be used for subscription products
     *
     * @param null $store
     * @return array
     */
    public function getAllowedSubscriptionPaymentMethodCodes($store = null)
    {
        $paymentMethodCodes = array();

        if ($config = Mage::getStoreConfig(self::ALLOWED_SUBSCRIPTION_PAYMENTS_PATH, $store)) {
            $paymentMethodCodes = explode(',', $config);
        }

        return $paymentMethodCodes;
    }


    /**
     * Checks if payment identified by its code has a subscription implementation
     *
     * @param $paymentMethodCode
     * @return bool
     */
    public function isSubscriptionPayment($paymentMethodCode)
    {
        // TODO: maybe try to see if we can instantiate subscription method
        $allowedPaymentMethods = $this->getAllowedSubscriptionPaymentMethodCodes();

        return in_array($paymentMethodCode, $allowedPaymentMethods);
    }


    /**
     * Checks if notifications for expired payment are enabled
     *
     * @return bool
     */
    public function isExpiredPaymentNotificationEnabled()
    {
        return (boolean)Mage::getStoreConfig(self::EXPIRED_PAYMENT_NOTIFICATION_PATH);
    }


    /**
     * Returns number of days before payment expiration when notification is going to be sent
     *
     * @return int
     */
    public function getDaysBeforeExpiredPaymentNotification()
    {
        $config = (int)Mage::getStoreConfig(self::DAYS_BEFORE_EXPIRED_PAYMENT_NOTIFICATION_PATH);

        return $config ?: 5;
    }


    /**
     * Returns expiration date that needs to be checked based on specified current date
     *
     * @param string|null $currentDate
     * @return string
     */
    public function getExpirationDateThreshold($currentDate=null)
    {
        $currentDate = $currentDate ?: Mage::getSingleton('core/date')->gmtDate();
        $days = $this->getDaysBeforeExpiredPaymentNotification();

        $expirationDate = date('Y-m-d', strtotime("+{$days} days {$currentDate}"));

        return $expirationDate;
    }

}
