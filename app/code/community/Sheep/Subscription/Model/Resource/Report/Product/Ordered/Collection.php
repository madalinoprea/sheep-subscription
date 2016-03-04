<?php

/**
 * Class Sheep_Subscription_Model_Resource_Report_Product_Ordered_Collection offers a way to see how many skus were ordered (as recurring or as non recurring).
 *
 * Also it offers an option to include only items ordered that were not part of a renewal order.
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2016, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Model_Resource_Report_Product_Ordered_Collection extends Sheep_Subscription_Model_Resource_Report_Product_Best_Collection
{
    protected $_excludeRenewals = false;


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
            array('oi' => 'sales/order_item'),
            'product_id = entity_id',
            array(
                'product_name' => 'name',
                'counts' => 'COUNT(1)',
                'sum_qty' => "SUM(CASE WHEN oip.product_type IN ('configurable', 'bundle') THEN oip.qty_ordered * oi.qty_ordered ELSE oi.qty_ordered END)",
                'total' => "SUM(CASE WHEN oip.product_type='configurable' THEN oip.base_row_total ELSE oi.base_row_total END)",
                'product_type',
                'order_id' => 'order_id',
                'parent_item_id' => 'parent_item_id',
                'item_created_at' => 'created_at'
            )
        );
        $this->addFilterToMap('parent_item_id', 'oi.parent_item_id');
        $this->addFilterToMap('product_type', 'oi.product_type');
        $this->addFilterToMap('item_created_at', 'item_created_at');

        // Retrieve base currency code from parent order
        $this->joinTable(
            array('o' => 'sales/order'),
            'entity_id = order_id',
            array('currency_code' => 'base_currency_code', 'store_id' => 'store_id')
        );

        // re-join sales_flat_quote_item to retrieve parent info (if available)
        $this->joinTable(
            array('oip' => 'sales/order_item'),
            'item_id = parent_item_id',
            array('parent_product_type' => 'product_type', 'parent_qty' => 'qty_ordered'),
            null,
            'left'
        );
        $this->addFilterToMap('parent_product_type', 'oip.product_type');
        $this->addFilterToMap('parent_qty', 'oip.qty_ordered');

        // Filter only cart items created during specified period
        $this->addFieldToFilter('item_created_at', array(
            'from' => $this->_from,
            'to' => $this->_to,
            'datetime' => true
        ));

        $this->getSelect()->where(
            "(oi.parent_item_id is null and oi.product_type NOT IN ('bundle', 'configurable')) OR" .
            "(oi.parent_item_id is not null and oip.product_type IN ('bundle', 'configurable'))"
        );

        // Filter our items that belong to orders that were created as a renewal order
        if ($this->_excludeRenewals) {
            $this->joinTable(
                array('r' => 'sheep_subscription/renewal'),
                'order_id = order_id',
                array('renewal_id' => 'id'),
                'r.id IS NULL',
                'left'
            );
        }

        $this->getSelect()
            ->group('e.entity_id')
            ->order('counts ' . self::SORT_ORDER_DESC)
            ->having('COUNT(oi.product_id) > ?', 0);


        // Filter only quotes that were created in specified stores
        if ($this->_storeIds) {
            $this->addFieldToFilter('store_id', array('in' => $this->_storeIds));
        }
    }


    /**
     * @param boolean $excludeRenewals
     */
    public function setExcludeRenewals($excludeRenewals)
    {
        $this->_excludeRenewals = $excludeRenewals;
    }

}
