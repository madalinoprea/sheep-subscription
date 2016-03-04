<?php

/**
 * Class Sheep_Subscription_Model_Resource_Report_Subscription_New_Collection
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2016, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Model_Resource_Report_Subscription_New_Collection extends Sheep_Subscription_Model_Resource_Subscription_Collection
{
    protected $_from;
    protected $_to;
    protected $_storeIds = array();


    /**
     * Resets current collection and sets apropiate conditions that were passed via setDateRange and setStoreIds
     *
     * @throws Mage_Core_Exception
     */
    public function prepareCollection()
    {
        $this->addFieldToSelect(array('id', 'status', 'created_at'));
        $this->addQuoteData(array('store_id', 'base_subtotal', 'base_grand_total', 'base_currency_code'));

        $this->addFieldToFilter('main_table.created_at', array(
            'from' => $this->_from,
            'to' => $this->_to,
            'datetime' => true
        ));

        if ($this->_storeIds) {
            $this->addFieldToFilter('q.store_id', array('in' => $this->_storeIds));
            $this->getSelect()->where('q.store_id IN (?)', $this->_storeIds);
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
        $this->_fieldsToSelect = null;
        $this->_joinedTables = array();
        return parent::_reset();
    }


    /**
     * Makes sure collections is prepared before loading it
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
