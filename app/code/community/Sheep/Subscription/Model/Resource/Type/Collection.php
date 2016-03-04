<?php

/**
 * Class Sheep_Subscription_Model_Resource_Type_Collection
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Model_Resource_Type_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('sheep_subscription/type');
    }

    /**
     * Filters subscription types by store
     * @deprecated Reserved for future use
     * @param $store
     * @return $this
     */
    public function addStoreFilter($store)
    {
        return $this;
    }

    /**
     * Filters subscription types by status
     * @param $status
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function addStatusFilter($status)
    {
        return $this->addFieldToFilter('status', $status);
    }
}
