<?php

/**
 * Class Sheep_Subscription_Block_Subscription_View
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Block_Subscription_View extends Sheep_Subscription_Block_Subscription_Abstract
{
    /**
     * Returns urls to subscription list in account dashboard
     *
     * @return string
     */
    public function getBackUrl()
    {
        return Mage::helper('sheep_subscription')->getSubscriptionListUrl();
    }


    /**
     * Returns url to specified order
     *
     * @param $orderId
     * @return string
     */
    public function getOrderUrl($orderId)
    {
        return $this->getUrl('sales/order/view', array('order_id' => $orderId));
    }


    /**
     * Returns order status label for specified status
     *
     * @param $orderStatus
     * @return string
     */
    public function getOrderStatusLabel($orderStatus)
    {
        return Mage::getSingleton('sales/order_config')->getStatusLabel($orderStatus);
    }


    /**
     * Returns edit subscription shipping address url
     *
     * @return string
     */
    public function getEditShippingAddressUrl()
    {
        return Mage::helper('sheep_subscription')->getEditShippingAddressUrl($this->getSubscription()->getId());
    }


    /**
     * Returns edit subscription shipping method url
     *
     * @return string
     */
    public function getEditShippingMethodUrl()
    {
        return Mage::helper('sheep_subscription')->getEditShippingMethodUrl($this->getSubscription()->getId());
    }


    /**
     * Checks if is allowed to edit shipping information
     *
     * @return bool
     */
    public function canChangeShippingInformation()
    {
        return Mage::getModel('sheep_subscription/service')->canChangeShippingInformation($this->getSubscription());
    }


    /**
     * Checks if is allowed to edit payment information
     *
     * @return boolean
     */
    public function canChangePaymentInformation()
    {
        return Mage::getModel('sheep_subscription/service')->canChangePaymentInformation($this->getSubscription());
    }


    /**
     * Returns subscription change payment url
     *
     * @return string
     */
    public function getChangePaymentUrl()
    {
        return Mage::helper('sheep_subscription')->getEditPaymentUrl($this->getSubscription()->getId());
    }


    /**
     * Adds to layout links and payment info block
     */
    protected function _prepareLayout()
    {
        $subscription = $this->getSubscription();

        $this->setChild(
            'payment_info',
            $this->helper('payment')->getInfoBlock($subscription->getPayment())
        );
    }

}
