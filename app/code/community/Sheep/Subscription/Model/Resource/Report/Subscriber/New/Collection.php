<?php

/**
 * Class Sheep_Subscription_Model_Resource_Report_Subscriber_New_Collection
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2016, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Model_Resource_Report_Subscriber_New_Collection extends Mage_Customer_Model_Resource_Customer_Collection
{
    protected $_from;
    protected $_to;
    protected $_storeIds = array();


    /**
     * Resets current collection and sets apropiate conditions that were passed via setDateRange and setStoreIds
     * @throws Mage_Core_Exception
     */
    public function prepareCollection()
    {
        $this->_reset();
        $this->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $this->getSelect()->columns(array('email'));

        if ($this->_storeIds) {
            $this->addAttributeToFilter('store_id', array('in' => $this->_storeIds));
        }

        // Find all customers that had their first subscription created during this period
        $this->joinTable(
            array('s' => 'sheep_subscription/subscription'),
            'customer_id = entity_id',
            array('created_at')
        );

        $this->getSelect()->having('MIN(s.created_at) > ?', $this->_from);
        $this->getSelect()->having('MIN(s.created_at) < ?', $this->_to);
        $this->getSelect()->group('email');
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
        $this->_from = $from;
        $this->_to = $to;

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
        $this->_storeIds = $storeIds;

        return $this;
    }


    /**
     * Reinit current collection before the actual load
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function load($printQuery = false, $logQuery = false)
    {
        $this->prepareCollection();

        return parent::load($printQuery, $logQuery);
    }
}
