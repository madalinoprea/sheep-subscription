<?php
/**
 * Class Sheep_Subscription_IndexController
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */

class Sheep_Subscription_IndexController extends Sheep_Subscription_Controller_Front_Action
{

    /**
     * Subscription list action
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->_title($this->__('My Subscriptions'));
        $this->renderLayout();
    }


    /**
     * Subscription view action
     *
     * @return Mage_Core_Controller_Varien_Action
     */
    public function viewAction()
    {
        $subscription = $this->_initSubscription();
        if (!$subscription) {
            Mage::getSingleton('customer/session')->addNotice('Unable to find specified subscription.');
            return $this->_redirect('subscriptions/index/index');
        }

        Mage::register('pss_subscription', $subscription);

        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->_title($this->__('My Subscriptions'))
            ->_title($this->__('Subscription %s', $subscription->getId()));
        $this->renderLayout();
    }


    /**
     * Subscription edit shipping address action
     *
     * @return Mage_Core_Controller_Varien_Action
     */
    public function editShippingAddressAction()
    {
        $subscription = $this->_initSubscription();
        if (!$subscription) {
            Mage::getSingleton('customer/session')->addNotice('Unable to find specified subscription.');
            return $this->_redirect('subscriptions/index/index');
        }

        Mage::register('pss_subscription', $subscription);

        $this->loadLayout();
        $this->initLayoutMessages('customer/session');
        $this->_title($this->__('My Subscriptions'))
            ->_title($this->__('Subscription %s', $subscription->getId()))
            ->_title($this->__('Edit Shipping Information'));
        $this->renderLayout();
    }


    /**
     * Subscription edit shipping method action
     *
     * @return Mage_Core_Controller_Varien_Action
     */
    public function editShippingMethodAction()
    {
        $subscription = $this->_initSubscription();
        if (!$subscription) {
            Mage::getSingleton('customer/session')->addNotice('Unable to find specified subscription.');
            return $this->_redirect('subscriptions/index/index');
        }

        Mage::register('pss_subscription', $subscription);

        $this->loadLayout();
        $this->initLayoutMessages('customer/session');
        $this->_title($this->__('My Subscriptions'))
            ->_title($this->__('Subscription %s', $subscription->getId()))
            ->_title($this->__('Edit Shipping Information'));
        $this->renderLayout();
    }


    public function editPaymentAction()
    {
        $subscription = $this->_initSubscription();
        if (!$subscription) {
            Mage::getSingleton('customer/session')->addNotice('Unable to find specified subscription.');
            return $this->_redirect('subscriptions/index/index');
        }

        Mage::register('pss_subscription', $subscription);

        $this->loadLayout();
        $this->initLayoutMessages('customer/session');
        $this->_title($this->__('My Subscriptions'))
            ->_title($this->__('Subscription %s', $subscription->getId()))
            ->_title($this->__('Edit Payment Information'));
        $this->renderLayout();
    }

}
