<?php
/**
 * Class Sheep_Subscription_Block_History
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */

class Sheep_Subscription_Block_History extends Mage_Core_Block_Template
{
    protected $subscriptions;


    /**
     * Returns id for current customer session
     * @return int|null
     */
    public function getCustomerId()
    {
        return Mage::getSingleton('customer/session')->getCustomerId();
    }


    /**
     * Returns subscription associated to current customer
     *
     * @return Sheep_Subscription_Model_Resource_Subscription_Collection
     */
    public function getSubscriptions()
    {
        if ($this->subscriptions===null) {
            $this->subscriptions = Mage::helper('sheep_subscription/subscription')->getCustomerSubscriptions($this->getCustomerId());
            $this->subscriptions->addNextRenewalDate();
            $this->subscriptions->addOrder('status', Varien_Data_Collection_Db::SORT_ORDER_ASC);
            $this->subscriptions->addOrder('renewal_date', Varien_Data_Collection_Db::SORT_ORDER_ASC);
        }

        return $this->subscriptions;
    }


    /**
     * Adds pager to layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $pager = $this->getLayout()->createBlock('page/html_pager', 'sheep.subscriptions.history.pager');
        $pager->setCollection($this->getSubscriptions());
        $this->setChild('pager', $pager);

        return $this;
    }


    /**
     * Returns pager html
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }


    /**
     * Return subscription view url
     *
     * @param Sheep_Subscription_Model_Subscription $subscription
     * @return string
     */
    public function getViewUrl($subscription)
    {
        return Mage::helper('sheep_subscription')->getSubscriptionUrl($subscription->getId());
    }

}
