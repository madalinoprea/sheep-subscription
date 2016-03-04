<?php

/**
 * Class Sheep_Subscription_Helper_Type
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Helper_Type extends Mage_Core_Helper_Abstract
{

    /**
     * Returns subscription type options
     * @return array
     */
    public function getStatusOptions()
    {
        return array(
            Sheep_Subscription_Model_Type::STATUS_ENABLED  => $this->__('Enabled'),
            Sheep_Subscription_Model_Type::STATUS_DISABLED => $this->__('Disabled')
        );
    }

    /**
     * Returns subscription type period unit options
     * @return array
     */
    public function getPeriodUnitOptions()
    {
        return array(
            Sheep_Subscription_Model_Type::PERIOD_UNIT_DAYS   => $this->__('Days'),
            Sheep_Subscription_Model_Type::PERIOD_UNIT_WEEKS  => $this->__('Weeks'),
            Sheep_Subscription_Model_Type::PERIOD_UNIT_MONTHS => $this->__('Months'),
            Sheep_Subscription_Model_Type::PERIOD_UNIT_YEARS  => $this->__('Years')
        );
    }

    /**
     * Returns subscription type finite/infinite as option
     * @return array
     */
    public function getIsInfiniteOptions()
    {
        return array(
            Sheep_Subscription_Model_Type::IS_FINITE   => $this->__('Finite'),
            Sheep_Subscription_Model_Type::IS_INFINITE => $this->__('Infinite'),
        );
    }

    /**
     * Returns subscription type trial options
     * @return array
     */
    public function getHasTrialOptions()
    {
        return array(
            Sheep_Subscription_Model_Type::WITHOUT_TRIAL => $this->__('Without Trial'),
            Sheep_Subscription_Model_Type::HAS_TRIAL     => $this->__('With Trial')
        );
    }

    /**
     * Returns all subscription types assigned to specified store
     * @param null $store
     * @return Sheep_Subscription_Model_Resource_Type_Collection
     */
    public function getTypes($store = null)
    {
        $subscriptionTypes = Mage::getModel('sheep_subscription/type')->getCollection();
        $subscriptionTypes->addStoreFilter($store);

        return $subscriptionTypes;
    }

    /**
     * Returns all enabled subscription types associated to specified store
     * @param null $store
     * @return Sheep_Subscription_Model_Resource_Type_Collection
     */
    public function getAvailableTypes($store = null)
    {
        $subscriptionTypes = $this->getTypes($store);
        $subscriptionTypes->addStatusFilter(Sheep_Subscription_Model_Type::STATUS_ENABLED);

        return $subscriptionTypes;
    }

}
