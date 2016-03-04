<?php

/**
 * Class Sheep_Subscription_Helper_Data
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Helper_Data extends Mage_Core_Helper_Abstract
{
    const ACCOUNT_ALLOW_MANAGEMENT_PATH = 'sheep_subscription/account/allow_management';
    const ACCOUNT_ALLOW_RENEWAL_DATE_EDIT_PATH = 'sheep_subscription/account/allow_renewal_date_edit';
    const ACCOUNT_ALLOW_SHIPPING_INFO_EDIT_PATH = 'sheep_subscription/account/allow_shipping_info_edit';
    const ACCOUNT_ALLOW_PAYMENT_INFO_EDIT_PATH = 'sheep_subscription/account/allow_payment_info_edit';

    /**
     * Returns frontend url for subscription list
     *
     * @return string
     */
    public function getSubscriptionListUrl()
    {
        return $this->_getUrl('subscriptions/index/index');
    }


    /**
     * Returns frontend url for subscription page
     *
     * @param int $subscriptionId
     * @return string
     */
    public function getSubscriptionUrl($subscriptionId)
    {
        return $this->_getUrl('subscriptions/index/view', array('subscription_id' => $subscriptionId));
    }


    /**
     * Returns frontend url for subscription page
     *
     * @param int $subscriptionId
     * @param $storeId
     * @return string
     */
    public function getSubscriptionUrlInStore($subscriptionId, $storeId)
    {
        return $this->_getUrl('subscriptions/index/view', array(
            'subscription_id' => $subscriptionId,
            '_store' => $storeId
        ));
    }


    /**
     * Returns frontend pause subscription url
     *
     * @param int $subscriptionId
     * @return string
     */
    public function getPauseSubscriptionUrl($subscriptionId)
    {
        return $this->_getUrl('subscriptions/service/pause', array('subscription_id' => $subscriptionId));
    }


    /**
     * Returns frontend resume subscription url
     *
     * @param int $subscriptionId
     * @return string
     */
    public function getResumeSubscriptionUrl($subscriptionId)
    {
        return $this->_getUrl('subscriptions/service/resume', array('subscription_id' => $subscriptionId));
    }


    /**
     * Returns frontend cancel subscription url
     *
     * @param int $subscriptionId
     * @return string
     */
    public function getCancelSubscriptionUrl($subscriptionId)
    {
        return $this->_getUrl('subscriptions/service/cancel', array('subscription_id' => $subscriptionId));
    }


    /**
     * Returns frontend change renewal date url
     *
     * @param int $subscriptionId
     * @return string
     */
    public function getChangeRenewalDateUrl($subscriptionId)
    {
        return $this->_getUrl('subscriptions/service/changeRenewalDate', array('subscription_id' => $subscriptionId));
    }


    /**
     * Returns frontend edit subscription shipping address
     *
     * @param int $subscriptionId
     * @return string
     */
    public function getEditShippingAddressUrl($subscriptionId)
    {
        return $this->_getUrl('subscriptions/index/editShippingAddress', array('subscription_id' => $subscriptionId));
    }


    /**
     * Returns service url to save subscription shipping address
     *
     * @param int $subscriptionId
     * @return string
     */
    public function getSaveShippingAddressUrl($subscriptionId)
    {
        return $this->_getUrl('subscriptions/service/saveShippingAddress', array('subscription_id' => $subscriptionId));
    }


    /**
     * Returns frontend edit subscription shipping method
     *
     * @param int $subscriptionId
     * @return string
     */
    public function getEditShippingMethodUrl($subscriptionId)
    {
        return $this->_getUrl('subscriptions/index/editShippingMethod', array('subscription_id' => $subscriptionId));
    }


    /**
     * Returns service url that saves subscription shipping method
     *
     * @param int $subscriptionId
     * @return string
     */
    public function getSaveShippingMethodUrl($subscriptionId)
    {
        return $this->_getUrl('subscriptions/service/saveShippingMethod', array('subscription_id' => $subscriptionId));
    }


    /**
     * Returns frontend edit payment information url
     *
     * @param int $subscriptionId
     * @return string
     */
    public function getEditPaymentUrl($subscriptionId)
    {
        return $this->_getUrl('subscriptions/index/editPayment', array('subscription_id' => $subscriptionId));
    }


    /**
     * Returns service url that adds subscriptions's recurring product to current cart
     *
     * @param int $subscriptionId
     * @return string
     */
    public function getAddSubscriptionToCartUrl($subscriptionId)
    {
        return $this->_getUrl('subscriptions/service/addToCart', array('subscription_id' => $subscriptionId));
    }


    /**
     * Checks if customer are allowed to manage their subscription from their account
     *
     * @param null $store
     * @return bool
     */
    public function getIsAccountManagementAllowed($store = null)
    {
        return (boolean)Mage::getStoreConfig(self::ACCOUNT_ALLOW_MANAGEMENT_PATH, $store);
    }


    /**
     * Check is customers are allowed to change date for their current renewal date
     *
     * @param null $store
     * @return bool
     */
    public function getIsAccountRenewalEditAllowed($store = null)
    {
        return (boolean)Mage::getStoreConfig(self::ACCOUNT_ALLOW_RENEWAL_DATE_EDIT_PATH, $store);
    }


    /**
     * Check is customers are allowed to change shipping information on their subscription
     *
     * @param null $store
     * @return bool
     */
    public function getIsAccountShippingEditAllowed($store = null)
    {
        return (boolean)Mage::getStoreConfig(self::ACCOUNT_ALLOW_SHIPPING_INFO_EDIT_PATH, $store);
    }


    /**
     * Checks if customers are allowed to change payment information on subscription
     * @param null $store
     * @return bool
     */
    public function getIsAccountPaymentEditAllowed($store = null)
    {
        return (boolean)Mage::getStoreConfig(self::ACCOUNT_ALLOW_PAYMENT_INFO_EDIT_PATH, $store);
    }


}
