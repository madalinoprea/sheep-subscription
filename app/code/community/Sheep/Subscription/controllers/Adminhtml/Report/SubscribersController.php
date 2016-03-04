<?php

/**
 * Class Sheep_Subscription_Adminhtml_Report_Subscribers
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2016, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Adminhtml_Report_SubscribersController extends Sheep_Subscription_Controller_Adminhtml_Action
{

    protected function _isAllowed()
    {
        return $this->getAcl()->canViewNewSubscribersReport();
    }


    public function newAction()
    {
        $this->loadLayout();
        $this->_title('Reports')->_title('Subscriptions')->_title('New Subscribers');
        $this->_setActiveMenu('report/sheep_subscription/new_subscribers');
        $this->_addContent($this->getLayout()->createBlock('sheep_subscription/adminhtml_report_subscribers_new'));
        $this->renderLayout();
    }


    public function exportNewCsvAction()
    {
        $fileName = 'new_subscribers.csv';
        $content = $this->getLayout()->createBlock('sheep_subscription/adminhtml_report_subscribers_new_grid')->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
    }


    public function exportNewExcelAction()
    {
        $fileName = 'new_subscribers.xml';
        $content = $this->getLayout()->createBlock('sheep_subscription/adminhtml_report_subscribers_new_grid')->getExcel($fileName);

        $this->_prepareDownloadResponse($fileName, $content);
    }

}
