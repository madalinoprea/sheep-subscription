<?php

/**
 * Class Sheep_Subscription_ServiceController offers actions that change subscription status
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_ServiceController extends Sheep_Subscription_Controller_Front_Action
{
    /**
     * Returns subscription service
     *
     * @return Sheep_Subscription_Model_Service
     */
    protected function getService()
    {
        return Mage::getModel('sheep_subscription/service');
    }


    /**
     * Checks if customers are allowed to manage their subscriptions
     *
     * @return bool
     */
    public function isManagementAllowed()
    {
        return Mage::helper('sheep_subscription')->getIsAccountManagementAllowed();
    }


    /**
     * Pause subscription action
     *
     * @return Mage_Core_Controller_Varien_Action
     */
    public function pauseAction()
    {
        $helper = Mage::helper('sheep_subscription');
        $subscription = $this->_initSubscription();
        if (!$subscription) {
            Mage::getSingleton('customer/session')->addNotice($this->__('Unable to find specified subscription.'));
            return $this->_redirectUrl($helper->getSubscriptionListUrl());
        }

        if (!$this->isManagementAllowed()) {
            Mage::getSingleton('customer/session')->addNotice($this->__('Subscription management is not allowed.'));
            return $this->_redirectUrl($helper->getSubscriptionListUrl());
        }

        $service = $this->getService();
        if (!$service->canBePaused($subscription)) {
            Mage::getSingleton('customer/session')->addNotice($this->__('Specified subscription cannot be paused.'));
            return $this->_redirectUrl($helper->getSubscriptionUrl($subscription->getId()));
        }

        try {
            $service->pauseSubscription($subscription);
            Mage::getSingleton('customer/session')->addSuccess($this->__('Subscription # %s  was paused.', $subscription->getId()));
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::getSingleton('customer/session')->addError($this->__('Specified subscription cannot be paused.'));
        }

        return $this->_redirectUrl($helper->getSubscriptionUrl($subscription->getId()));
    }


    /**
     * Resume subscription action
     *
     * @return Mage_Core_Controller_Varien_Action
     */
    public function resumeAction()
    {
        $helper = Mage::helper('sheep_subscription');
        $subscription = $this->_initSubscription();
        if (!$subscription) {
            Mage::getSingleton('customer/session')->addNotice($this->__('Unable to find specified subscription.'));
            return $this->_redirectUrl($helper->getSubscriptionListUrl());
        }

        if (!$this->isManagementAllowed()) {
            Mage::getSingleton('customer/session')->addNotice($this->__('Subscription management is not allowed.'));
            return $this->_redirectUrl($helper->getSubscriptionListUrl());
        }

        $service = $this->getService();
        if (!$service->canBeResumed($subscription)) {
            Mage::getSingleton('customer/session')->addNotice($this->__('Specified subscription cannot be resumed.'));
            return $this->_redirectUrl($helper->getSubscriptionUrl($subscription->getId()));
        }

        try {
            $service->resumeSubscription($subscription);
            Mage::getSingleton('customer/session')->addSuccess($this->__('Subscription # %s  was resumed.', $subscription->getId()));
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::getSingleton('customer/session')->addError($this->__('Specified subscription cannot be resumed.'));
        }

        return $this->_redirectUrl($helper->getSubscriptionUrl($subscription->getId()));
    }


    /**
     * Cancel subscription action
     *
     * @return Mage_Core_Controller_Varien_Action
     */
    public function cancelAction()
    {
        $helper = Mage::helper('sheep_subscription');
        $subscription = $this->_initSubscription();
        if (!$subscription) {
            Mage::getSingleton('customer/session')->addNotice($this->__('Unable to find specified subscription.'));
            return $this->_redirectUrl($helper->getSubscriptionListUrl());
        }

        if (!$this->isManagementAllowed()) {
            Mage::getSingleton('customer/session')->addNotice($this->__('Subscription management is not allowed.'));
            return $this->_redirectUrl($helper->getSubscriptionListUrl());
        }

        $service = $this->getService();
        if (!$service->canBeCancelled($subscription)) {
            Mage::getSingleton('customer/session')->addNotice($this->__('Specified subscription cannot be cancelled.'));
            return $this->_redirectUrl($helper->getSubscriptionUrl($subscription->getId()));
        }

        try {
            $service->cancelSubscription($subscription);
            Mage::getSingleton('customer/session')->addSuccess($this->__('Subscription # %s  was cancelled.', $subscription->getId()));
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::getSingleton('customer/session')->addError($this->__('Specified subscription cannot be cancelled.'));
        }

        return $this->_redirectUrl($helper->getSubscriptionUrl($subscription->getId()));
    }


    /**
     * Changes subscription renewal date
     *
     * @return Mage_Core_Controller_Varien_Action
     */
    public function changeRenewalDateAction()
    {
        $helper = Mage::helper('sheep_subscription');
        $subscription = $this->_initSubscription();

        if (!$subscription) {
            Mage::getSingleton('customer/session')->addNotice($this->__('Unable to find specified subscription.'));
            return $this->_redirectUrl($helper->getSubscriptionListUrl());
        }

        $date = $this->getRequest()->getPost('renewal_date', '');

        // Internally we store our date in UTC timezone, so we have to add 12h to client locale date
        $dateTime = strtotime("{$date} +12 hours");
        if ($dateTime==false) {
            Mage::getSingleton('customer/session')->addNotice('Please specify a valid date.');
            return $this->_redirectUrl($helper->getSubscriptionUrl($subscription->getId()));
        }
        $date = date('Y-m-d H:i:s', $dateTime);


        $service  = $this->getService();
        if (!$service->canChangeRenewalDate($subscription)) {
            Mage::getSingleton('customer/session')->addNotice($this->__('Cannot change renewal date for specified subscription.'));
            return $this->_redirectUrl($helper->getSubscriptionUrl($subscription->getId()));
        }

        try {
            $service->changeRenewalDate($subscription, $date);
            Mage::getSingleton('customer/session')->addSuccess($this->__('Renewal date for subscription # %s was changed.', $subscription->getId()));
        } catch (Exception $e) {
            Mage::getSingleton('customer/session')->addError($this->__('Cannot change date for renewal: %s', $e->getMessage()));
        }

        return $this->_redirectUrl($helper->getSubscriptionUrl($subscription->getId()));
    }


    /**
     * Changes subscription shipping address
     *
     * @return Mage_Core_Controller_Varien_Action
     */
    public function saveShippingAddressAction()
    {
        $customerSession = $this->getSession();
        $helper = Mage::helper('sheep_subscription');
        $subscription = $this->_initSubscription();
        if (!$subscription) {
            return $this->_redirectReferer($helper->getSubscriptionListUrl());
        }

        if ($subscription->getQuote()->isVirtual()) {
            $customerSession->addNotice('You cannot save shipping address on virtual subscription.');
            return $this->_redirectReferer($helper->getSubscriptionUrl($subscription->getId()));
        }

        $editShippingAddressUrl  = $helper->getEditShippingAddressUrl($subscription->getId());

        if ($this->getRequest()->isPost()) {
            $service = $this->getService();

            $errors = array();
            $customerAddressId = (int)$this->getRequest()->getPost('shipping_address_id');
            if ($customerAddressId) {
                $errors = $service->setSubscriptionShippingAddress($subscription, $customerAddressId);
            } else {
                $addressData = $this->getRequest()->getPost('shipping', array());
                $service->setSubscriptionShippingAddressData($subscription, $addressData);
            }

            // Validate address info
            if ($errors) {
                foreach ($errors as $error) {
                    $customerSession->addNotice($this->__($error));
                }
                return $this->_redirectReferer($editShippingAddressUrl);
            }

            // Success
            $customerSession->addSuccess('Subscription shipping address was updated.');

            // Ask customer to choose shipping method if shipping method is not longer available for new address
            if (!$subscription->getQuote()->getShippingAddress()->getShippingMethod()) {
                return $this->_redirectUrl($helper->getEditShippingMethodUrl($subscription->getId()));
            }

            return $this->_redirectUrl($helper->getSubscriptionUrl($subscription->getId()));
        }
    }


    /**
     * Changes subscription shipping method
     *
     * @return Mage_Core_Controller_Varien_Action
     */
    public function saveShippingMethodAction()
    {
        $customerSession = Mage::getSingleton('customer/session');
        $helper = Mage::helper('sheep_subscription');
        $subscription = $this->_initSubscription();
        if (!$subscription) {
            return $this->_redirectReferer($helper->getSubscriptionListUrl());
        }

        if ($subscription->getQuote()->isVirtual()) {
            $customerSession->addNotice('You cannot save shipping method on virtual subscription.');
            return $this->_redirectReferer($helper->getSubscriptionUrl($subscription->getId()));
        }

        $editMethodUrl  = $helper->getEditShippingMethodUrl($subscription->getId());

        if ($this->getRequest()->isPost()) {
            $service = $this->getService();

            $shippingMethod = $this->getRequest()->getPost('shipping_method', '');
            $errors = $service->setSubscriptionShippingMethod($subscription, $shippingMethod);

            // Do we have any errors?
            if ($errors) {
                foreach ($errors as $error) {
                    $customerSession->addNotice($this->__($error));
                }

                return $this->_redirectUrl($editMethodUrl);
            }

            // Success
            $customerSession->addSuccess($this->__('Subscription shipping method was updated.'));
            $this->_redirectUrl($helper->getSubscriptionUrl($subscription->getId()));
        }
    }


    /**
     * Adds products referenced by subscription in current cart
     * @return Mage_Core_Controller_Varien_Action
     */
    public function addToCartAction()
    {
        $customerSession = Mage::getSingleton('customer/session');
        $helper = Mage::helper('sheep_subscription');
        $subscription = $this->_initSubscription();
        if (!$subscription) {
            $customerSession->addNotice('Specified subscription was not found.');
            return $this->_redirectReferer($helper->getSubscriptionListUrl());
        }

        $subscriptionViewUrl  = $helper->getSubscriptionUrl($subscription->getId());

        if ($this->getRequest()->isPost()) {
            $service = $this->getService();
            try {
                $service->addSubscriptionToCart($subscription);

                $this->getCheckoutSession()->addSuccess($this->__('Subscription products were added to your cart'));
                return $this->_redirectUrl(Mage::getUrl('checkout/cart'));
            } catch (Exception $e) {
                $customerSession->addError('Cannot add subscription products to current cart.');
                Mage::logException($e);
            }
        }

        return $this->_redirectReferer($subscriptionViewUrl);
    }
}
