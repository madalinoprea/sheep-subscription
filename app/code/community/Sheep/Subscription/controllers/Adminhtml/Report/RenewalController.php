<?php

/**
 * Class Sheep_Subscription_Adminhtml_Report_RenewalController
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2016, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Adminhtml_Report_RenewalController extends Sheep_Subscription_Controller_Adminhtml_Action
{

    protected function _isAllowed()
    {
        return
            $this->getAcl()->canViewScheduledRenewalsReport() ||
            $this->getAcl()->canViewRenewalTimelineReport();
    }


    /**
     * Shows already scheduled renewals
     */
    public function scheduledAction()
    {
        if (!$this->getAcl()->canViewScheduledRenewalsReport()) {
            return $this->forwardDenied();
        }

        $this->loadLayout();
        $this->_title('Reports')->_title('Subscriptions')->_title('Scheduled Renewals');
        $this->_setActiveMenu('report/sheep_subscription/scheduled_renewals');
        $this->_addContent($this->getLayout()->createBlock('sheep_subscription/adminhtml_report_renewal_schedule'));
        $this->renderLayout();
    }


    /**
     * Exports already scheduled renewals report as CSV
     */
    public function exportScheduledCsvAction()
    {
        if (!$this->getAcl()->canViewScheduledRenewalsReport()) {
            return $this->forwardDenied();
        }

        $fileName = 'scheduled_renewals.csv';
        $content = $this->getLayout()->createBlock('sheep_subscription/adminhtml_report_renewal_schedule_grid')->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
    }


    /**
     * Exports already scheduled renewals report as Excel XML
     */
    public function exportScheduledExcelAction()
    {
        if (!$this->getAcl()->canViewScheduledRenewalsReport()) {
            return $this->forwardDenied();
        }

        $fileName = 'scheduled_renewals.xml';
        $content = $this->getLayout()->createBlock('sheep_subscription/adminhtml_report_renewal_schedule_grid')->getExcel($fileName);

        $this->_prepareDownloadResponse($fileName, $content);
    }


    /**
     * Shows estimated renewal timeline
     */
    public function timelineAction()
    {
        if (!$this->getAcl()->canViewRenewalTimelineReport()) {
            return $this->forwardDenied();
        }

        $this->loadLayout();
        $this->_title('Reports')->_title('Subscriptions')->_title('Renewal Timeline');
        $this->_setActiveMenu('report/sheep_subscription/renewal_timeline');
        $this->_addContent($this->getLayout()->createBlock('sheep_subscription/adminhtml_report_renewal_timeline'));
        $this->renderLayout();
    }


    /**
     * Exports estimated renewals report as CSV
     */
    public function exportTimelineCsvAction()
    {
        if (!$this->getAcl()->canViewRenewalTimelineReport()) {
            return $this->forwardDenied();
        }

        $fileName = 'timeline_renewals.csv';
        $content = $this->getLayout()->createBlock('sheep_subscription/adminhtml_report_renewal_timeline_grid')->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
    }


    /**
     * Exports estimated renewals report as Excel XML
     */
    public function exportTimelineExcelAction()
    {
        if (!$this->getAcl()->canViewRenewalTimelineReport()) {
            return $this->forwardDenied();
        }

        $fileName = 'timeline_renewals.xml';
        $content = $this->getLayout()->createBlock('sheep_subscription/adminhtml_report_renewal_timeline_grid')->getExcel($fileName);

        $this->_prepareDownloadResponse($fileName, $content);
    }

}
