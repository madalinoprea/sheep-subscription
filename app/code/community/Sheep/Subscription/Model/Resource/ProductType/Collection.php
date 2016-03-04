<?php
/**
 * Class Sheep_Subscription_Model_Resource_ProductType_Collection
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */ 
class Sheep_Subscription_Model_Resource_ProductType_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    protected function _construct()
    {
        $this->_init('sheep_subscription/productType');
    }

    /**
     * Filters by specified product id
     * @param int $productId
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function addProductToFilter($productId)
    {
        return $this->addFieldToFilter('product_id', $productId);
    }

    /**
     * Returns products subscription types associated to a subscription type
     * @param int $subscriptionTypeId
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function addSubscriptionTypeFilter($subscriptionTypeId)
    {
        return $this->addFieldToFilter('type_id', $subscriptionTypeId);
    }
}
