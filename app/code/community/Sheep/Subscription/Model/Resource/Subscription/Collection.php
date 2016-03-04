<?php

/**
 * Class Sheep_Subscription_Model_Resource_Subscription_Collection
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Model_Resource_Subscription_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    /**
     * Initialise subscription collection with model uri
     */
    protected function _construct()
    {
        $this->_init('sheep_subscription/subscription');
    }


    /**
     * Adds subscription type filter
     *
     * @param int $typeId
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function addTypeFilter($typeId)
    {
        return $this->addFieldToFilter('type_id', $typeId);
    }

    /**
     * Adds customer filter
     *
     * @param $customerId
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function addCustomerFilter($customerId)
    {
        return $this->addFieldToFilter('main_table.customer_id', $customerId);
    }


    /**
     * Adds status filter
     *
     * @param $status
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function addStatusFilter($status)
    {
        return $this->addFieldToFilter('main_table.status', $status);
    }


    /**
     * Adds order filter
     *
     * @param int $orderId
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function addOrderFilter($orderId)
    {
        return $this->addFieldToFilter('original_order_id', $orderId);
    }


    /**
     * Retrieves specified quote fields from with associated subscription quote
     *
     * @param $quoteFields
     * @return $this
     */
    public function addQuoteData($quoteFields)
    {
        $this->join(
            array('q' => 'sales/quote'),
            'q.entity_id = main_table.quote_id',
            $quoteFields
        );

        return $this;
    }


    /**
     * Filters subscriptions based on quote's customer e-mail
     *
     * @param $customerEmail
     * @return $this
     */
    public function addCustomerEmailFilter($customerEmail)
    {
        $this->getSelect()->where('q.customer_email LIKE ?', "%$customerEmail%");

        return $this;
    }


    /**
     * Filters subscriptions based on quote's subtotal
     *
     * @param null $minimum
     * @param null $maximum
     * @return $this
     */
    public function addQuoteSubtotalFilter($minimum = null, $maximum = null)
    {
        if ($minimum !== null) {
            $this->getSelect()->where('q.subtotal >= ?', $minimum);
        }
        if ($maximum !== null) {
            $this->getSelect()->where('q.subtotal <= ?', $maximum);
        }

        return $this;
    }


    /**
     * Retrieves specified quote fields from with associated subscription quote
     *
     * @return $this
     */
    public function addNextRenewalDate()
    {
        $this->getSelect()->joinLeft(
            array('r' => $this->getTable('sheep_subscription/renewal')),
            'r.subscription_id = main_table.id AND r.status IN (' . implode(', ', array(Sheep_Subscription_Model_Renewal::STATUS_PENDING, Sheep_Subscription_Model_Renewal::STATUS_PROCESSING, Sheep_Subscription_Model_Renewal::STATUS_WAITING)) . ')',
            array('renewal_date' => 'r.date')
        );

        return $this;
    }
}
