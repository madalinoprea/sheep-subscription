<?php

/**
 * Class Sheep_Subscription_Model_Resource_Report_Product_Best_Collection
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2016, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Model_Resource_Report_Product_Best_Collection extends Mage_Reports_Model_Resource_Product_Collection
{
    protected $_from;
    protected $_to;
    protected $_storeIds = array();


    /**
     * Resets current collection and sets appropriate conditions that were passed via setDateRange and setStoreIds
     *
     * @throws Mage_Core_Exception
     */
    public function prepareCollection()
    {
        $this->_reset();

        $this->addAttributeToSelect('*');

        // Select cart items
        $this->joinTable(
            array('qi' => 'sales/quote_item'),
            'product_id = entity_id',
            array(
                'product_name' => 'name',
                'counts' => 'COUNT(1)',
                'sum_qty' => "SUM(CASE WHEN qip.product_type IN ('configurable', 'bundle') THEN qip.qty * qi.qty ELSE qi.qty END)",
                'total' => "SUM(CASE WHEN qip.product_type='configurable' THEN qip.base_row_total ELSE qi.base_row_total END)",
                'product_type',
                'quote_id' => 'quote_id',
                'parent_item_id' => 'parent_item_id',
                'item_created_at' => 'created_at'
            )
        );
        $this->addFilterToMap('parent_item_id', 'qi.parent_item_id');
        $this->addFilterToMap('product_type', 'qi.product_type');
        $this->addFilterToMap('item_created_at', 'item_created_at');

        // Retrieve base currency code from parent quote
        $this->joinTable(
            array('q' => 'sales/quote'),
            'entity_id = quote_id',
            array('currency_code' => 'base_currency_code', 'store_id' => 'store_id')
        );

        // Select only cart items attached to a subscription
        $this->joinTable(
            array('s' => 'sheep_subscription/subscription'),
            'quote_id = quote_id',
            array('subscription_created_at' => 'created_at',)
        );

        // re-join sales_flat_quote_item to retrieve parent info (if available)
        $this->joinTable(
            array('qip' => 'sales/quote_item'),
            'item_id = parent_item_id',
            array('parent_product_type' => 'product_type', 'parent_qty' => 'qty'),
            null,
            'left'
        );
        $this->addFilterToMap('parent_product_type', 'qip.product_type');
        $this->addFilterToMap('parent_qty', 'qip.qty');

        // Filter only cart items created during specified period
        $this->addFieldToFilter('item_created_at', array(
            'from' => $this->_from,
            'to' => $this->_to,
            'datetime' => true
        ));

        $this->getSelect()->where(
            "(qi.parent_item_id is null and qi.product_type NOT IN ('bundle', 'configurable')) OR" .
            "(qi.parent_item_id is not null and qip.product_type IN ('bundle', 'configurable'))"
        );

        $this->getSelect()
            ->group('e.entity_id')
            ->order('counts ' . self::SORT_ORDER_DESC)
            ->having('COUNT(qi.product_id) > ?', 0);


        // Filter only quotes that were created in specified stores
        if ($this->_storeIds) {
            $this->addFieldToFilter('store_id', array('in' => $this->_storeIds));
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

    protected function _afterLoad()
    {
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
