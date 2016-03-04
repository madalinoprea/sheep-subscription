<?php

/**
 * Class Sheep_Subscription_Block_Adminhtml_Report_Inventory_Planning_Grid
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2016, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Block_Adminhtml_Report_Inventory_Planning_Grid extends Mage_Adminhtml_Block_Report_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('gridInventoryPlanning');
        $this->_subReportSize = 0;
    }


    protected function _prepareCollection()
    {
        parent::_prepareCollection();

        /** @var Mage_Reports_Model_Resource_Report_Collection $collection */
        $collection = $this->getCollection();
        $collection->initReport('sheep_subscription/report_inventory_planning_collection');
    }


    protected function _prepareColumns()
    {
        $this->addColumn('product_name', array(
            'header' => Mage::helper('reports')->__('Product Name'),
            'index'  => 'name',
        ));

        $this->addColumn('product_sku', array(
            'header' => Mage::helper('reports')->__('Product Sku'),
            'index'  => 'sku',
        ));

        $this->addColumn('initial_qty', array(
            'header' => Mage::helper('reports')->__('Qty at start period'),
            'index' => 'initial_qty'
        ));

        $this->addColumn('ordered_qty', array(
            'header' => Mage::helper('reports')->__('Onetime Estimated Qty'),
            'index' => 'avg_qty'
        ));

        $this->addColumn('required_qty', array(
            'header' => Mage::helper('reports')->__('Renewal Estimated Qty'),
            'index'  => 'qty',
            'type'   => 'number'
        ));

        $this->addColumn('current_qty', array(
            'header' => Mage::helper('reports')->__('Current Qty'),
            'index'  => 'current_qty',
            'type'   => 'number'
        ));

        $this->addColumn('missing_qty', array(
            'header' => Mage::helper('reports')->__('Missing Qty'),
            'index'  => 'missing_qty',
            'type'   => 'number'
        ));

        $this->addExportType('*/*/exportPlanningCsv', Mage::helper('reports')->__('CSV'));
        $this->addExportType('*/*/exportPlanningExcel', Mage::helper('reports')->__('Excel XML'));

        parent::_prepareColumns();
    }

}
