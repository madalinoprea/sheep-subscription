<?php

/**
 * Class Sheep_Subscription_Block_Adminhtml_Subscription_View_Tabs_Info
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Block_Adminhtml_Subscription_View_Tabs_Info
    extends Mage_Adminhtml_Block_Widget

{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('sheep_subscription/info.phtml');
    }

    /**
     * Returns current subscription
     *
     * @return Sheep_Subscription_Model_Subscription
     */
    public function getSubscription()
    {
        return Mage::registry('pss_subscription');
    }

    /**
     * Adds children blocks responsible to render payment information, subscription items and subscription totals
     * @return Mage_Core_Block_Abstract
     */
    protected function _prepareLayout()
    {
        $this->setChild(
            'payment_info',
            $this->helper('payment')->getInfoBlock($this->getSubscription()->getPayment())
        );
        $this->setChild(
            'subscription_items',
            $this->getLayout()->createBlock('sheep_subscription/adminhtml_subscription_items')
        );
        $this->setChild(
            'subscription_totals',
            $this->getLayout()->createBlock('sheep_subscription/subscription_totals')->setTemplate('sheep_subscription/subscription/totals.phtml')
            );
        return parent::_prepareLayout();
    }

    /**
     * Returns subscription items html
     *
     * @return string
     */
    public function getSubscriptionItems()
    {
        return $this->getChildHtml('subscription_items');
    }

    /**
     * Returns subscription totals html
     *
     * @return string
     */
    public function getSubscriptionTotals()
    {
        return $this->getChildHtml('subscription_totals');
    }

}
