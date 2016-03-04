<?php

/**
 * Class Sheep_Subscription_Adminhtml_SubscriptionController adds subscription admin actions
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Adminhtml_SubscriptionController extends Sheep_Subscription_Controller_Adminhtml_Action
{

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        $acl = $this->getAcl();

        return
            $acl->canShowProductSubscriptionTab() ||
            $acl->canViewCustomerSubscription() ||
            $acl->canViewSubscriptions() ||
            $acl->canViewSubscriptionDetails() ||
            $acl->canViewSubscriptionRenewals() ||
            $acl->canEditSubscription();
    }

    
    /**
     * Initializes current subscription based on request subscription_id param
     *
     * @return Sheep_Subscription_Model_Subscription|null
     */
    public function _initSubscription()
    {
        $subscriptionId = (int)$this->getRequest()->getParam('subscription_id');
        $subscription = Mage::getModel('sheep_subscription/subscription')->load($subscriptionId);
        if (!$subscription->getId()) {
            $this->_getSession()->addError(
                Mage::helper('sheep_subscription')->__('This subscription no longer exits.')
            );
            $this->_redirect('*/*/');
            return null;
        }

        return $subscription;
    }


    /**
     * Initialises product referenced in request in product_id parameter
     */
    public function _initCurrentProduct()
    {
        /** @var Mage_Catalog_Model_Product $product */
        $product = Mage::getModel('catalog/product');
        $productId = (int)$this->getRequest()->getParam('product_id', null);

        if ($productId) {
            $product->load($productId);
        }
        Mage::register('pss_current_product', $product);
    }


    /**
     * Subscription grid
     */
    public function indexAction()
    {
        if (!$this->getAcl()->canViewSubscriptions()) {
            return $this->forwardDenied();
        }

        $this->loadLayout();
        $this->_addContent($this->getLayout()->createBlock('sheep_subscription/adminhtml_subscription'));
        $this->_title('Orders')->_title('Subscriptions');
        $this->renderLayout();
    }


    /**
     * Export subscriptions as csv
     */
    public function exportCsvAction()
    {
        if (!$this->getAcl()->canViewSubscriptions()) {
            return $this->forwardDenied();
        }

        $fileName = 'Subscription_export.csv';
        $content = $this->getLayout()->createBlock('sheep_subscription/adminhtml_subscription_grid')->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }


    /**
     * Edit subscription
     */
    public function viewAction()
    {
        if (!$this->getAcl()->canViewSubscriptionDetails()) {
            return $this->forwardDenied();
        }

        if (!$subscription = $this->_initSubscription()) {
            return;
        }

        $data = $this->_getSession()->getFormData(true);
        if ($data) {
            $subscription->setData($data);
        }

        Mage::register('pss_subscription', $subscription);

        $this->loadLayout();
        $this->_title('Sales')->_title('Subscriptions')->_title('#' . $subscription->getId());
        $this->_addLeft($this->getLayout()->createBlock('sheep_subscription/adminhtml_subscription_view_tabs'))
            ->_addContent($this->getLayout()->createBlock('sheep_subscription/adminhtml_subscription_view'));
        $this->renderLayout();
    }


    /**
     * Pause subscription action
     */
    public function pauseAction()
    {
        if (!$this->getAcl()->canEditSubscription()) {
            return $this->forwardDenied();
        }

        if (!$subscription = $this->_initSubscription()) {
            return;
        }

        try {
            $service = Mage::getModel('sheep_subscription/service');
            $service->pauseSubscription($subscription);
            $this->_getSession()->addSuccess($this->__('Subscription #%s was paused', $subscription->getId()));
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('Specified subscription cannot be paused: %s', $e->getMessage()));
        }

        $this->_redirect('*/*/view', array('subscription_id' => $subscription->getId()));
    }


    /**
     * Resume subscription action
     */
    public function resumeAction()
    {
        if (!$this->getAcl()->canEditSubscription()) {
            return $this->forwardDenied();
        }

        if (!$subscription = $this->_initSubscription()) {
            return;
        }

        try {
            $service = Mage::getModel('sheep_subscription/service');
            $service->resumeSubscription($subscription);
            $this->_getSession()->addSuccess($this->__('Subscription #%s was resumed.', $subscription->getId()));
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('Specified subscription cannot be resumed: %s', $e->getMessage()));
        }

        $this->_redirect('*/*/view', array('subscription_id' => $subscription->getId()));
    }


    /**
     * Cancel subscription action
     */
    public function cancelAction()
    {
        if (!$this->getAcl()->canEditSubscription()) {
            return $this->forwardDenied();
        }

        if (!$subscription = $this->_initSubscription()) {
            return;
        }

        try {
            $service = Mage::getModel('sheep_subscription/service');
            $service->cancelSubscription($subscription);
            $this->_getSession()->addSuccess($this->__('Subscription #%s was canceled.', $subscription->getId()));
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('Specified subscription cannot be canceled: %s', $e->getMessage()));
        }

        $this->_redirect('*/*/view', array('subscription_id' => $subscription->getId()));
    }



    /**
     * Renewals grid tab
     */
    public function renewalsTabAction()
    {
        if (!$this->getAcl()->canViewSubscriptionRenewals()) {
            return $this->forwardDenied();
        }

        $subscriptionId = (int)$this->getRequest()->getParam('subscription_id');
        /** @var Sheep_Subscription_Block_Adminhtml_Subscription_View_Tabs_Renewals $block */
        $block = $this->getLayout()->createBlock('sheep_subscription/adminhtml_subscription_view_tabs_renewals');
        $block->setSubscriptionId($subscriptionId);

        $this->getResponse()->setBody($block->toHtml());
    }

    /**
     * Action that rebuilds subscription grid via ajax
     */
    public function gridAction()
    {
        if (!$this->getAcl()->canViewSubscriptions()) {
            return $this->forwardDenied();
        }

        /** @var Sheep_Subscription_Block_Adminhtml_Subscription_Grid $block */
        $block = $this->getLayout()->createBlock('sheep_subscription/adminhtml_subscription_grid');
        $this->getResponse()->setBody($block->toHtml());
    }

    /**
     * Action to build customer subscriptions tab
     */
    public function customerTabAction()
    {
        if (!$this->getAcl()->canShowCustomerSubscriptionTab()) {
            return;
        }

        $customerId = (int)$this->getRequest()->getParam('customer_id', 0);
        /** @var Sheep_Subscription_Block_Adminhtml_Customer_Edit_Tab_Subscriptions $block */
        $block = $this->getLayout()->createBlock('sheep_subscription/adminhtml_customer_edit_tab_subscriptions');
        $block->setCustomerId($customerId);

        $this->getResponse()->setBody($block->toHtml());
    }

    /**
     * Action to build subscription tab on product
     */
    public function configurationProductTabAction()
    {
        if (!$this->getAcl()->canShowProductSubscriptionTab()) {
            return $this->forwardDenied();
        }

        $this->_initCurrentProduct();

        $block = $this->getLayout()->createBlock('sheep_subscription/adminhtml_catalog_product_edit_tab_subscription');
        $this->getResponse()->setBody($block->toHtml());
    }

}
