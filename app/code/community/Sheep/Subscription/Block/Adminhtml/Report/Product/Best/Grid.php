<?php

/**
 * Class Sheep_Subscription_Block_Adminhtml_Report_Product_Best_Grid
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2016, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Block_Adminhtml_Report_Product_Best_Grid extends Mage_Adminhtml_Block_Report_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('gridBestProducts');
        $this->_subReportSize = 0;
    }


    protected function _prepareCollection()
    {
        parent::_prepareCollection();

        /** @var Mage_Reports_Model_Resource_Report_Collection $collection */
        $collection = $this->getCollection();
        $collection->initReport('sheep_subscription/report_product_best_collection');
    }


    protected function _prepareColumns()
    {
        $this->addColumn('sku', array(
            'header' => Mage::helper('reports')->__('Sku'),
            'index'  => 'sku',
        ));

        $this->addColumn('product_name', array(
            'header' => Mage::helper('reports')->__('Product Name'),
            'index'  => 'product_name'
        ));

        $this->addColumn('counts', array(
            'header' => Mage::helper('reports')->__('Count'),
            'index'  => 'counts',
            'type'   => 'number',
            'total'  => 'sum',
        ));

        $this->addColumn('sum_qty', array(
            'header' => Mage::helper('reports')->__('Quantity'),
            'index'  => 'sum_qty',
            'type'   => 'number',
            'total'  => 'sum',
        ));

        $this->addColumn('total', array(
            'header'   => Mage::helper('reports')->__('Total'),
            'index'    => 'total',
            'type'     => 'price',
            'currency' => 'currency_code',
            'total'    => 'sum',
        ));

        $this->addExportType('*/*/exportBestCsv', Mage::helper('reports')->__('CSV'));
        $this->addExportType('*/*/exportBestExcel', Mage::helper('reports')->__('Excel XML'));

        parent::_prepareColumns();
    }

}
