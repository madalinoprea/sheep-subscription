<?php

/**
 * Class Sheep_Subscription_Model_Resource_Report_Subscription_Canceled_Collection
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2016, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Model_Resource_Report_Subscription_Canceled_Collection extends Sheep_Subscription_Model_Resource_Report_Subscription_New_Collection
{

    /**
     * Resets current collection and sets appropriate conditions that were passed via setDateRange and setStoreIds
     *
     * @throws Mage_Core_Exception
     */
    public function prepareCollection()
    {
        $this->addFieldToSelect(array('id', 'status', 'created_at'));
        $this->addQuoteData(array('store_id', 'base_subtotal', 'base_grand_total', 'base_currency_code'));

        $this->addStatusFilter(Sheep_Subscription_Model_Subscription::STATUS_CANCELLED);
        $this->addFieldToFilter('main_table.canceled_at', array(
            'from' => $this->_from,
            'to' => $this->_to,
            'datetime' => true
        ));

        if ($this->_storeIds) {
            $this->addFieldToFilter('q.store_id', array('in' => $this->_storeIds));
            $this->getSelect()->where('q.store_id IN (?)', $this->_storeIds);
        }
    }

}
