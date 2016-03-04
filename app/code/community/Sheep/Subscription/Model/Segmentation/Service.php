<?php

/**
 * Class Sheep_Subscription_Model_Segmentation_Service
 *
 * @category Sheep
 * @package Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Model_Segmentation_Service
{
    const DEFAULT_CUSTOMER_GROUP_PROMOTION_PATH = 'sheep_subscription/segmentation/default_customer_group_promotion';
    const DEFAULT_CUSTOMER_GROUP_DEMOTION_PATH = 'sheep_subscription/segmentation/default_customer_group_demotion';

    public function log($message, $level = Zend_Log::INFO)
    {
        Mage::log(__CLASS__ . ': ' . $message, $level);
    }


    /**
     * Returns customer group id that will be associated to a customer that purchased a new subscription
     *
     * @param null $store
     * @return int
     */
    public function getPromotionGroupId($store=null)
    {
        return (int)Mage::getStoreConfig(self::DEFAULT_CUSTOMER_GROUP_PROMOTION_PATH, $store);
    }


    /**
     * Returns customer group id that will be associated to a customer de-active his last active subscription
     *
     * @param null $store
     * @return int
     */
    public function getDemotionGroupId($store=null)
    {
        return (int)Mage::getStoreConfig(self::DEFAULT_CUSTOMER_GROUP_DEMOTION_PATH, $store);
    }


    /**
     * @param Mage_Customer_Model_Customer $customer
     * @param int $customerGroupId
     */
    public function assignCustomerGroup(Mage_Customer_Model_Customer $customer, $customerGroupId)
    {
        // Verify customer group is valid
        $customerGroup = Mage::getModel('customer/group')->load($customerGroupId);
        if (!$customerGroup->getId()) {
            $this->log("Cannot move customer customerId={$customer->getId()} into missing groupId={$customerGroupId}");
            return;
        }

        $customer->setGroupId($customerGroupId);
        $customer->save();
    }


    /**
     * Moves customer to promotion customer group
     *
     * @param Mage_Customer_Model_Customer $customer
     */
    public function promoteCustomer(Mage_Customer_Model_Customer $customer)
    {
        $groupId = $this->getPromotionGroupId($customer->getStoreId());
        $this->assignCustomerGroup($customer, $groupId);
    }


    /**
     * Moves customer to demotion customer group if he does not have any active subscriptions
     *
     * @param Mage_Customer_Model_Customer $customer
     */
    public function demoteCustomer(Mage_Customer_Model_Customer $customer)
    {
        $groupId = $this->getDemotionGroupId($customer->getStoreId());
        $activeSubscription = Mage::helper('sheep_subscription/subscription')->getCustomerActiveSubscriptions($customer->getId());

        if ($activeSubscription->getSize() == 0) {
            $this->assignCustomerGroup($customer, $groupId);
        }
    }
}
