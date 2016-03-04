<?php

/**
 * Class Sheep_Subscription_Model_Resource_Report_Renewal_Schedule_Collection
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2016, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Model_Resource_Report_Renewal_Schedule_Collection extends Sheep_Subscription_Model_Resource_Renewal_Collection
{
    protected $_from;
    protected $_to;
    protected $_storeIds = array();


    /**
     * Applies saved filters and selects data required by report grid
     *
     * @throws Mage_Core_Exception
     */
    public function prepareCollection()
    {
        $this->addBetweenFilter($this->_from, $this->_to);

        $this->addSubscriptionData(array('start_date'));
        $this->addSubscriptionQuoteData(array('store_id', 'base_subtotal', 'base_grand_total', 'base_currency_code'));
        $this->addRenewalOrderData(array('order_status' => 'status', 'order_base_grand_total' => 'base_grand_total'));

        $this->addFilterToMap('store_id', 'q.store_id');
        $this->addFieldToFilter('store_id', array('in' => $this->_storeIds));
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
        $this->_fieldsToSelect = null;
        $this->_joinedTables = array();
        return parent::_reset();
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
        return parent::load($printQuery, true);
    }

}
