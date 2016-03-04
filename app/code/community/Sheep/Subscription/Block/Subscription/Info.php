<?php

/**
 * Class Sheep_Subscription_Block_Subscription_Info
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Block_Subscription_Info extends Sheep_Subscription_Block_Subscription_Abstract
{
    protected $_links = array();

    /**
     * Adds link
     *
     * @param $label
     * @param $path
     */
    public function addLink($label, $path)
    {
        $this->_links[$label] = new Varien_Object(array(
            'label' => $label,
            'url' => $path ?: '#'
        ));
    }


    /**
     * Returns links
     *
     * @return array
     */
    public function getLinks()
    {
        return $this->_links;
    }


    /**
     * Returns subscription service
     *
     * @return Sheep_Subscription_Model_Service
     */
    public function getService()
    {
        return Mage::getModel('sheep_subscription/service');
    }


    /**
     * Check if we need to display subcription management buttons (pause, resume, cancel)
     *
     * @return boolean
     */
    public function allowCustomerManagement()
    {
        return Mage::helper('sheep_subscription')->getIsAccountManagementAllowed();
    }


    /**
     * Adds subscription management links if allowed
     */
    public function addSubscriptionManagementLinks()
    {
        if (!$this->allowCustomerManagement()) {
            return;
        }

        $subscription = $this->getSubscription();
        $helper = $this->helper('sheep_subscription');
        $service = $this->getService();

        if ($service->canBePaused($subscription)) {
            $this->addLink($this->__('Pause'), $helper->getPauseSubscriptionUrl($subscription->getId()));
        }

        if ($service->canBeResumed($subscription)) {
            $this->addLink($this->__('Resume'), $helper->getResumeSubscriptionUrl($subscription->getId()));
        }

        if ($service->canBeCancelled($subscription)) {
            $this->addLink($this->__('Cancel'), $helper->getCancelSubscriptionUrl($subscription->getId()));
        }
    }


    /**
     * Adds to layout links and payment info block
     */
    protected function _prepareLayout()
    {
        $subscription = $this->getSubscription();

        /** @var Mage_Page_Block_Html_Head $headBlock */
        if ($headBlock = $this->getLayout()->getBlock('head')) {
            $headBlock->setTitle($this->__('Subscription # %s', $subscription->getId()));
        }

        if ($subscription->getOriginalOrderId()) {
            $this->addLink(
                $this->__('View Original Order'),
                $this->getUrl('sales/order/view', array('order_id' => $subscription->getOriginalOrderId()))
            );
        }

        $this->addSubscriptionManagementLinks();
    }


    /**
     * Checks if we can change renewal date
     *
     * @return bool
     */
    public function canChangeRenewalDate()
    {
        return $this->getService()->canChangeRenewalDate($this->getSubscription());
    }


    /**
     * Returns next renewal date as string using format supported by date input type
     *
     * @return string
     */
    public function getFormattedRenewalDate()
    {
        $formattedDate = '';
        if ($this->getSubscription()->getNextRenewal()) {
            $storeDate = $this->getSubscription()->getNextRenewal()->getDateStoreDate();
            $formattedDate = $storeDate->toString('YYYY-MM-dd');
        }

        return $formattedDate;
    }


}
