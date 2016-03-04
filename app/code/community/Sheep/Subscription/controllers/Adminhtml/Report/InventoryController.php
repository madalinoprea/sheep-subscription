<?php

/**
 * Class Sheep_Subscription_Adminhtml_Report_InventoryController
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2016, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Adminhtml_Report_InventoryController extends Sheep_Subscription_Controller_Adminhtml_Action
{

    protected function _isAllowed()
    {
        return $this->getAcl()->canViewInventoryPlanningReport();
    }


    /**
     * Shows inventory requirements for unprocessed renewals from a specified period
     */
    public function planningAction()
    {
        $this->loadLayout();
        $this->_title('Reports')->_title('Subscriptions')->_title('Inventory Planning');
        $this->_setActiveMenu('report/sheep_subscription/inventory_planning');
        $this->_addContent($this->getLayout()->createBlock('sheep_subscription/adminhtml_report_inventory_planning'));
        $this->renderLayout();
    }


    /**
     * Exports inventory requirements for un-processed renewals as CSV
     */
    public function exportPlanningCsvAction()
    {
        $fileName = 'inventory_planning.csv';
        $content = $this->getLayout()->createBlock('sheep_subscription/adminhtml_report_inventory_planning_grid')->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
    }


    /**
     * Exports inventory requirements for un-processed renewals as Excel XML
     */
    public function exportPlanningExcelAction()
    {
        $fileName = 'inventory_planning.xml';
        $content = $this->getLayout()->createBlock('sheep_subscription/adminhtml_report_inventory_planning_grid')->getExcel($fileName);

        $this->_prepareDownloadResponse($fileName, $content);
    }

}
