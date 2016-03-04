<?php

/**
 * Class Sheep_Subscription_Adminhtml_Report_ProductController
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2016, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Adminhtml_Report_ProductController extends Sheep_Subscription_Controller_Adminhtml_Action
{

    protected function _isAllowed()
    {
        return $this->getAcl()->canViewBestRecurringProductsReport();
    }


    /**
     * Shows best recurring products report
     */
    public function bestAction()
    {
        $this->loadLayout();
        $this->_title('Reports')->_title('Subscriptions')->_title('Best Products');
        $this->_setActiveMenu('report/sheep_subscription/best_recurring_products');
        $this->_addContent($this->getLayout()->createBlock('sheep_subscription/adminhtml_report_product_best'));
        $this->renderLayout();
    }


    /**
     * Exports best recurring products report as CSV
     */
    public function exportBestCsvAction()
    {
        $fileName = 'best_recurring_products.csv';
        $content = $this->getLayout()->createBlock('sheep_subscription/adminhtml_report_product_best_grid')->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
    }


    /**
     * Exports best recurring products report as Excel XML
     */
    public function exportBestExcelAction()
    {
        $fileName = 'best_recurring_products.xml';
        $content = $this->getLayout()->createBlock('sheep_subscription/adminhtml_report_product_best_grid')->getExcel($fileName);

        $this->_prepareDownloadResponse($fileName, $content);
    }

}
