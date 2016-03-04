<?php

/**
 * Class Sheep_Subscription_Controller_Front_Action
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Controller_Front_Action extends Mage_Core_Controller_Front_Action
{
    /**
     * Returns current customer session
     *
     * @return Mage_Customer_Model_Session
     */
    public function getSession()
    {
        return Mage::getSingleton('customer/session');
    }


    /**
     * Returns current checkout session
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }


    /**
     * Returns current customer id
     *
     * @return int|null
     */
    protected function _getCustomerId()
    {
        return $this->getSession()->getCustomerId();
    }


    /**
     * Predispatch: checks if session is authenticated
     *
     * @return Mage_Core_Controller_Front_Action
     */
    public function preDispatch()
    {
        parent::preDispatch();
        if (!Mage::getSingleton('customer/session')->authenticate($this)) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }
    }


    /**
     * Returns subscription model based on current subscription id parameter.
     *
     * @return Sheep_Subscription_Model_Subscription|null
     */
    protected function _initSubscription()
    {
        $subscription = null;
        $subscriptionId = (int)$this->getRequest()->getParam('subscription_id');
        if ($subscriptionId) {
            /** @var Sheep_Subscription_Model_Subscription $subscription */
            $subscription = Mage::getModel('sheep_subscription/subscription')->load($subscriptionId);

            if (!$subscription->getId() || $subscription->getCustomerId() != $this->_getCustomerId()) {
                $subscription = null;
            }
        }

        return $subscription;
    }

}
