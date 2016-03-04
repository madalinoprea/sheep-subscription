<?php

/**
 * Class Sheep_Subscription_Model_Resource_Report_Inventory_Planning_Collection
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2016, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Model_Resource_Report_Inventory_Planning_Collection extends Varien_Data_Collection
{
    const QTY_ORDERED_AVG_PERIOD_PATH = 'sheep_subscription/reports/qty_ordered_avg_period';
    const STOCK_ITEM_NO_MANAGE_STOCK_VALUE = 'N/A';

    protected $_from;
    protected $_to;
    protected $_storeIds = array();

    protected $service;
    protected $subscriptionInventoryCache = array();
    protected $productInventory = array();
    /** @var Sheep_Subscription_Model_Resource_Report_Product_Ordered_Collection */
    protected $avgOrderedProducts;


    public function __construct()
    {
        parent::__construct();
        $this->service = Mage::getModel('sheep_subscription/service');
    }


    /**
     * Returns number of days contained in specied period
     *
     * @return float
     */
    public function getDaysInPeriod()
    {
        $dateDiff = abs(strtotime($this->_to) - strtotime($this->_from));

        return ceil($dateDiff / (60 * 60 * 24));
    }


    /**
     * Average ordered qty for a product is computed based on orders placed during prev days specified
     *
     * @return int
     */
    public function getDaysUsedForAverage()
    {
        $config = (int)Mage::getStoreConfig(self::QTY_ORDERED_AVG_PERIOD_PATH);
        return $config ?: 30;
    }


    /**
     * Returns an estimate of non-renewal qty that will be ordered for specified product based on prev orders.
     *
     * @param $productId
     * @return int
     */
    public function getAvgOrderedQty($productId)
    {
        // Use orders created in the last x days
        $numberOfDays = $this->getDaysUsedForAverage();

        // Init qty ordered collection
        if ($this->avgOrderedProducts == null) {
            /** @var Sheep_Subscription_Model_Resource_Report_Product_Ordered_Collection $report */
            $report = Mage::getResourceModel('sheep_subscription/report_product_ordered_collection');

            $today = new Zend_Date;
            $from = clone $today;
            $from = $from->subDay($numberOfDays);

            $report->setDateRange($from->toString(Zend_Date::ISO_8601), $today->toString(Zend_Date::ISO_8601));
            $report->setStoreIds($this->_storeIds);
            $report->setExcludeRenewals(true);

            $this->avgOrderedProducts = $report;
        }

        // Use 0 as an estimation if product was never purchased in the last x days
        $avgProductQtyOrdered = $this->avgOrderedProducts->getItemById($productId);
        if (!$avgProductQtyOrdered) {
            return 0;
        }

        // Use average per day to compute estimated ordered qty in this period
        return (int)($this->getDaysInPeriod() * ($avgProductQtyOrdered->getSumQty() / $numberOfDays));
    }


    /**
     * Updates estimated stock for specified product.
     *
     * Is loading initial product stock qty and removes estimated non-renewal ordered qty. Then it will substract renewal qty to update qty balance.
     * estimate at the end of report period.
     *
     * @param $productId
     * @param $subtractQty
     * @return mixed
     */
    public function getProductInventory($productId, $subtractQty)
    {
        if (!array_key_exists($productId, $this->productInventory)) {
            $avgOrderedQty = $this->getAvgOrderedQty($productId);

            // load associated product stock
            $product = Mage::getModel('catalog/product');
            $product->setId($productId);
            /** @var Mage_CatalogInventory_Model_Stock_Item $productStock */
            $productStock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);


            // Init inventory with current stock - minus avg ordered qty from non subscription orders
            $this->productInventory[$productId] = $productStock->getManageStock() ? $productStock->getQty() - $avgOrderedQty : self::STOCK_ITEM_NO_MANAGE_STOCK_VALUE;
        }

        if ($this->productInventory != self::STOCK_ITEM_NO_MANAGE_STOCK_VALUE) {
            $this->productInventory[$productId] -= $subtractQty;
        }

        return $this->productInventory[$productId];
    }


    /**
     * Applies saved filters and selects data required by report grid
     *
     * @throws Mage_Core_Exception
     */
    public function prepareCollection()
    {
        $this->_setIsLoaded(true);

        // Get all active subscriptions
        /** @var Sheep_Subscription_Model_Resource_Subscription_Collection $subscriptions */
        $subscriptions = Mage::getModel('sheep_subscription/subscription')->getCollection();
        $subscriptions->addStatusFilter(Sheep_Subscription_Model_Subscription::STATUS_ACTIVE);
        $subscriptions->addNextRenewalDate();

        $this->addRenewalCount($subscriptions);

        // add inventory req for each subscription
        foreach ($subscriptions as $subscription) {
            $this->addInventoryRequirement($subscription);
        }
    }


    /**
     * Adds all skus with their qtys to current collection
     *
     * @param Sheep_Subscription_Model_Subscription $subscription
     * @throws Exception
     */
    public function addInventoryRequirement(Sheep_Subscription_Model_Subscription $subscription)
    {
        $nRenewals = $subscription->getRenewalCount(); // number of renewals expected in specified period

        // Current subscription doesn't have any renewal scheduled for current period
        if ($nRenewals <= 0) {
            return;
        }

        // Get a list of skus and their qtys contained in this subscription
        $renewalInventory = $this->getInventory($subscription);

        foreach ($renewalInventory as $itemData) {
            $productId = $itemData['product_id'];
            $inventoryRecord = $this->getItemById($productId);

            // Add new inventory req for this sku
            if (!$inventoryRecord) {
                $inventoryRecord = new Varien_Object();
                $inventoryRecord->setId($productId);
                $inventoryRecord->setSku($itemData['sku']);
                $inventoryRecord->setName($itemData['name']);
                $inventoryRecord->setInitialQty($this->getProductInventory($productId, 0));
                $inventoryRecord->setQty(0);
                $inventoryRecord->setAvgQty($this->getAvgOrderedQty($productId));
                $this->addItem($inventoryRecord);

                // Record one time qty decrements
                $this->getProductInventory($productId, $inventoryRecord->getAvgQty());
            }

            // Just increment qty if we already have an existing req for this sku
            $allRenewalsQty = $nRenewals * $itemData['qty'];
            $inventoryRecord->setQty($inventoryRecord->getQty() + $allRenewalsQty);

            // Update product inventory
            $this->getProductInventory($productId, $allRenewalsQty);

            // current estimated qty is initial qty at start of period, minus estimated ordered qtys for non recurring products and minus computed
            // renewal requirements for current period
            $currentQty = $inventoryRecord->getInitialQty() - $inventoryRecord->getAvgQty() - $inventoryRecord->getQty();

            $inventoryRecord->setCurrentQty($currentQty);
            $inventoryRecord->setMissingQty( $currentQty > 0 ? 0 : -1 *  $currentQty);
        }
    }


    /**
     * Returns recurring products from a subscription as an array of arrays that have sku, name and qty as keys
     *
     * @param Sheep_Subscription_Model_Subscription $subscription
     * @return array
     */
    public function getInventory(Sheep_Subscription_Model_Subscription $subscription)
    {
        // Reuse subscription inventory already calculated
        if (array_key_exists($subscription->getId(), $this->subscriptionInventoryCache)) {
            return $this->subscriptionInventoryCache[$subscription->getId()];
        }

        /** @var Mage_Sales_Model_Resource_Quote_Item_Collection $quoteItems */
        $quoteItems = Mage::getResourceModel('sales/quote_item_collection');

        // rejoin to add parent info
        $quoteItems->getSelect()->joinLeft(
            array('qip' => $quoteItems->getTable('sales/quote_item')),
            'qip.item_id = main_table.parent_item_id',
            array('parent_product_type' => 'product_type', 'parent_qty' => 'qty')
        );


        $quoteItems->addFilterToMap('quote_id', 'main_table.quote_id');
        $quoteItems->addFilterToMap('parent_product_type', 'qip.product_type');
        $quoteItems->addFilterToMap('parent_qty', 'qip.qty');

        // Select only cart items included in subscription quote
        $quoteItems->addFieldToFilter('quote_id', $subscription->getQuoteId());
        $quoteItems->getSelect()->where(
            "(main_table.parent_item_id is null and main_table.product_type NOT IN ('bundle', 'configurable')) OR" .
            "(main_table.parent_item_id is not null and qip.product_type IN ('bundle', 'configurable'))"
        );

        // Fill in qty with correct qty even for composite products
        $quoteItems->getSelect()->columns(array(
            'qty' => new Zend_Db_Expr("IF(qip.product_type IN ('configurable', 'bundle'), qip.qty * main_table.qty, main_table.qty)")
        ));


        $inventoryData = array();
        foreach ($quoteItems->getData() as $quoteItemData) {
            $inventoryData[$quoteItemData['item_id']] = array(
                'sku'        => $quoteItemData['sku'],
                'name'       => $quoteItemData['name'],
                'qty'        => $quoteItemData['qty'],
                'product_id' => $quoteItemData['product_id']
            );
        }

        return $this->subscriptionInventoryCache[$subscription->getId()] = $inventoryData;
    }


    /**
     * Add number of renewals that will occur for subscription during specified period
     *
     * TODO: Handle/ test finite subscriptions
     *
     * @param Sheep_Subscription_Model_Resource_Subscription_Collection $subscriptions
     */
    public function addRenewalCount(Sheep_Subscription_Model_Resource_Subscription_Collection $subscriptions)
    {
        $fromTimestamp = strtotime($this->_from);
        $toTimestamp = strtotime($this->_to);

        /** @var Sheep_Subscription_Model_Subscription $subscription */
        foreach ($subscriptions as $subscription) {
            $subscription->setRenewalCount(0);

            $nextPaymentDate = $subscription->getData('renewal_date');

            // Next renewal date is not set (this should always be set for active subscriptions)
            if (!$nextPaymentDate) {
                continue;
            }

            // There are no renewals in this period
            if (strtotime($nextPaymentDate) > $toTimestamp) {
                continue;
            }

            // We have at least one occurence because our next scheduled renewal is during current specified period
            if (strtotime($nextPaymentDate) > $fromTimestamp) {
                $subscription->setRenewalCount(1);
            }

            // Generate future renewals starting next renewal date and check if they are during selected period
            $renewal = $this->service->getNextRenewal($subscription, $nextPaymentDate);

            // stop renewal generation if current renewal date is after specified period
            while (strtotime($renewal->getDate()) < $toTimestamp) {
                // Add only generated renewals that are after start of period
                if (strtotime($renewal->getDate()) > $fromTimestamp) {
                    $subscription->setRenewalCount($subscription->getRenewalCount() + 1);
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
