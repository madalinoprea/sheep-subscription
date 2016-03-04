<?php

/**
 * Class Sheep_Subscription_Model_Renewal
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @method int getId()
 * @method Sheep_Subscription_Model_Renewal setId(int $value)
 * @method int getSubscriptionId()
 * @method Sheep_Subscription_Model_Renewal setSubscriptionId(int $value)
 * @method int getStatus()
 * @method Sheep_Subscription_Model_Renewal setStatus(int $value)
 * @method string getDate()
 * @method Sheep_Subscription_Model_Renewal setDate(string $value)
 * @method string getCreatedAt()
 * @method Sheep_Subscription_Model_Renewal setCreatedAt(string $value)
 * @method string getUpdatedAt()
 * @method Sheep_Subscription_Model_Renewal setUpdatedAt(string $value)
 * @method string getLastMessage()
 * @method Sheep_Subscription_Model_Renewal setLastMessage(string $value)
 * @method int getOrderId()
 * @method Sheep_Subscription_Model_Renewal setOrderId(int $value)
 * @method int getFailedPaymentsCount()
 * @method Sheep_Subscription_Model_Renewal setFailedPaymentsCount(int $value)
 */
class Sheep_Subscription_Model_Renewal extends Mage_Core_Model_Abstract
{
    const STATUS_PENDING = 10;       // Scheduled renewal - new (useful for self managed methods)
    const STATUS_PROCESSING = 20;    // Placed in queue for self managed methods
    const STATUS_WAITING = 25;       // Waiting for gateway managed payment methods
    const STATUS_PAYED = 30;         // Successful payed renewal
    CONST STATUS_FAILED = 50;        // Un-successful payed renewal

    protected $subscription;

    protected function _construct()
    {
        $this->_init('sheep_subscription/renewal');
    }

    protected function _beforeSave()
    {
        $now = Mage::getSingleton('core/date')->gmtDate();
        if ($this->isObjectNew()) {
            $this->setCreatedAt($now);
        }
        $this->setUpdatedAt($now);

        return parent::_beforeSave();
    }

    /**
     * Returns renewal status label
     *
     * @return string
     */
    public function getStatusLabel()
    {
        $helper = Mage::helper('sheep_subscription/renewal');
        $statusLabels = $helper->getStatusOptions();

        if (array_key_exists($this->getStatus(), $statusLabels)) {
            return $statusLabels[$this->getStatus()];
        } else {
            return $helper->__('N/A');
        }
    }

    /**
     * Checks if renewal is payed
     *
     * @return bool
     */
    public function isPayed()
    {
        return $this->getStatus() == self::STATUS_PAYED;
    }


    /**
     * Checks if renewal si failed
     *
     * @return bool
     */
    public function isFailed()
    {
        return $this->getStatus() == self::STATUS_FAILED;
    }


    /**
     * Returns subscription associated to this renewal
     *
     * @return Sheep_Subscription_Model_Subscription
     */
    public function getSubscription()
    {
        if ($this->subscription === null) {
            $this->subscription = Mage::getModel('sheep_subscription/subscription');
            if ($this->getSubscriptionId()) {
                $this->subscription->load($this->getSubscriptionId());
            }
        }

        return $this->subscription;
    }

    /**
     * Get renewal date with current store timezone
     *
     * @return Zend_Date
     */
    public function getDateStoreDate()
    {
        return Mage::app()->getLocale()->storeDate(
            null,
            Varien_Date::toTimestamp($this->getDate()),
            true
        );
    }

    /**
     * Creates an order for this renewal
     *
     * @return Mage_Sales_Model_Order
     * @throws Exception
     */
    public function createOrder()
    {
        $quote = $this->getSubscription()->getQuote();
        // Add extra attributes on subscription quote that can be used by payment method
        $quote->setPssSubscription($this->getSubscription());
        $quote->setPssRenewal($this);

        Mage::dispatchEvent('pss_renewal_create_order_before', array('renewal' => $this, 'quote' => $quote));

        $quote->collectTotals();
        $service = Mage::getModel('sales/service_quote', $quote);

        $service->submitAll();
        $order = $service->getOrder();

        if (!$order) {
            throw new Exception('Unable to create order');
        }

        Mage::dispatchEvent('pss_renewal_create_order_after', array('renewal' => $this, 'order' => $order));

        return $order;
    }
}
