<?php

/**
 * Class Sheep_Subscription_Model_Resource_Report_Renewal_Timeline_Collection
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2016, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Model_Resource_Report_Renewal_Timeline_Collection extends Varien_Data_Collection
{
    protected $_from;
    protected $_to;
    protected $_storeIds = array();

    protected $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = Mage::getModel('sheep_subscription/service');
    }


    /**
     * Applies saved filters and selects data required by report grid
     *
     * @throws Mage_Core_Exception
     */
    public function prepareCollection()
    {
        $scheduledRenewals = Mage::getModel('sheep_subscription/resource_renewal_collection');
        $scheduledRenewals->addBetweenFilter($this->_from, $this->_to);

        $scheduledRenewals->addFieldToSelect(array(
            'subscription_id',
            'date',
            'status',
            'latest_scheduled_date' => new Zend_Db_Expr('MAX(date)'),
        ));
        $scheduledRenewals->addSubscriptionData(array('subscription_status' => 'status', 'type_info', 'start_date'));
        $scheduledRenewals->addSubscriptionQuoteData(array('store_id', 'base_subtotal', 'base_grand_total', 'base_currency_code'));
        $scheduledRenewals->addRenewalOrderData(array('order_status' => 'status', 'order_base_grand_total' => 'base_grand_total'));

        $scheduledRenewals->addFilterToMap('store_id', 'q.store_id');
        $scheduledRenewals->addFieldToFilter('store_id', array('in' => $this->_storeIds));

        $scheduledRenewals->getSelect()->group('id');

        // Add renewals already scheduled
        /** @var Sheep_Subscription_Model_Renewal $renewal */
        foreach ($scheduledRenewals as $renewal) {
            $this->addItem($renewal);
        }

        $this->addFutureRenewals();
        $this->_setIsLoaded(true);
    }


    /**
     * Add future renewals for all active subscriptions that will have their date between current specified period
     *
     * TODO: Handle/ test finite subscriptions
     *
     * @throws Exception
     */
    public function addFutureRenewals()
    {
        // Get all active subscriptions
        /** @var Sheep_Subscription_Model_Resource_Subscription_Collection $subscriptions */
        $subscriptions = Mage::getModel('sheep_subscription/subscription')->getCollection();
        $subscriptions->addStatusFilter(Sheep_Subscription_Model_Subscription::STATUS_ACTIVE);
        $subscriptions->addQuoteData(array('base_subtotal', 'base_grand_total', 'base_currency_code'));
        $subscriptions->addNextRenewalDate();

        $toTimestamp = strtotime($this->_to);
        $fromTimestamp = strtotime($this->_from);

        foreach ($subscriptions as $subscription) {
            $lastPayedDate = $subscription->getData('renewal_date');

            if (strtotime($lastPayedDate) > $toTimestamp) {
                continue;
            }

            // Generate future renewals starting last renewal date
            $renewal = $this->service->getNextRenewal($subscription, $lastPayedDate);

            // stop renewal generation if current renewal date is after specified period
            while (strtotime($renewal->getDate()) < $toTimestamp) {
                // Add only generated renewals that are in specified period
                if (strtotime($renewal->getDate()) > $fromTimestamp) {
                    $renewal->setBaseSubtotal($subscription->getData('base_subtotal'));
                    $renewal->setBaseGrandTotal($subscription->getData('base_grand_total'));
                    $renewal->setBaseCurrencyCode($subscription->getData('base_currency_code'));

                    $this->addItem($renewal);
                }

                // continue renewal generation until we are after specified period
                $renewal = $this->service->getNextRenewal($subscription, $renewal->getDate());
            }
        }
    }


    /**
     * Date range selected by customer.
     *
     * Method required by @see Mage_Reports_Model_Report
     *
     * @param string $from
     * @param string $to
     * @return $this
     */
    public function setDateRange($from, $to)
    {
        if ($this->_from != $from || $this->_to != $to) {
            $this->_from = $from;
            $this->_to = $to;
            $this->_reset();
        }

        return $this;
    }


    /**
     * Store Id selected by customer.
     *
     * Method required by @see Mage_Reports_Model_Report
     *
     * @param $storeIds
     * @return $this
     */
    public function setStoreIds(array $storeIds)
    {
        if ($this->_storeIds != $storeIds) {
            $this->_storeIds = $storeIds;
            $this->_reset();
        }

        return $this;
    }


    /**
     * Resets current collection, also resets joined tables
     *
     * @return Varien_Data_Collection_Db
     */
    protected function _reset()
    {
        $this->clear();
    }


    /**
     * Makes sure collection is prepared before loading it
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return $this|Varien_Data_Collection_Db
     */
    public function load($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded()) {
            return $this;
        }

        $this->prepareCollection();

        return parent::load($printQuery, $logQuery);
    }

}
