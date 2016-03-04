<?php

/**
 * Class Sheep_Subscription_Adminhtml_Report_SubscriptionController
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2016, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Adminhtml_Report_SubscriptionController extends Sheep_Subscription_Controller_Adminhtml_Action
{

    protected function _isAllowed()
    {
        return
            $this->getAcl()->canViewNewSubscriptionsReport() ||
            $this->getAcl()->canViewCanceledSubscriptionsReport();
    }


    /**
     * New subscription report
     */
    public function newAction()
    {
        if (!$this->getAcl()->canViewNewSubscriptionsReport()) {
            return $this->forwardDenied();
        }

        $this->loadLayout();
        $this->_title('Reports')->_title('Subscriptions')->_title('New Subscriptions');
        $this->_setActiveMenu('report/sheep_subscription/new_subscriptions');
        $this->_addContent($this->getLayout()->createBlock('sheep_subscription/adminhtml_report_subscription_new'));
        $this->renderLayout();
    }


    /**
     * Exports new subscription report as CSV
     */
    public function exportNewCsvAction()
    {
        if (!$this->getAcl()->canViewNewSubscriptionsReport()) {
            return $this->forwardDenied();
        }

        $fileName = 'new_subscriptions.csv';
        $content = $this->getLayout()->createBlock('sheep_subscription/adminhtml_report_subscription_new_grid')->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
    }


    /**
     * Exports new subscription report as Excel XML
     */
    public function exportNewExcelAction()
    {
        if (!$this->getAcl()->canViewNewSubscriptionsReport()) {
            return $this->forwardDenied();
        }

        $fileName = 'new_subscriptions.xml';
        $content = $this->getLayout()->createBlock('sheep_subscription/adminhtml_report_subscription_new_grid')->getExcel($fileName);

        $this->_prepareDownloadResponse($fileName, $content);
    }


    /**
     * Shows canceled subscriptions report
     */
    public function canceledAction()
    {
        if (!$this->getAcl()->canViewCanceledSubscriptionsReport()) {
            return $this->forwardDenied();
        }

        $this->loadLayout();
        $this->_title('Reports')->_title('Subscriptions')->_title('Canceled Subscriptions');
        $this->_setActiveMenu('report/sheep_subscription/canceled_subscriptions');
        $this->_addContent($this->getLayout()->createBlock('sheep_subscription/adminhtml_report_subscription_canceled'));
        $this->renderLayout();
    }


    /**
     * Exports canceled subscriptions report as CSV
     */
    public function exportCanceledCsvAction()
    {
        if (!$this->getAcl()->canViewCanceledSubscriptionsReport()) {
            return $this->forwardDenied();
        }

        $fileName = 'canceled_subscriptions.csv';
        $content = $this->getLayout()->createBlock('sheep_subscription/adminhtml_report_subscription_canceled_grid')->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
    }


    /**
     * Exports canceled subscriptions report as Excel XML
     */
    public function exportCanceledExcelAction()
    {
        if (!$this->getAcl()->canViewCanceledSubscriptionsReport()) {
            return $this->forwardDenied();
        }

        $fileName = 'canceled_subscriptions.xml';
        $content = $this->getLayout()->createBlock('sheep_subscription/adminhtml_report_subscription_canceled_grid')->getExcel($fileName);

        $this->_prepareDownloadResponse($fileName, $content);
    }

}
