<?php

/**
 * Class Sheep_Subscription_Block_Adminhtml_Report_Renewal_Schedule_Grid
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2016, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Block_Adminhtml_Report_Renewal_Schedule_Grid extends Mage_Adminhtml_Block_Report_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('gridScheduledRenewals');
        $this->_subReportSize = 0;
    }


    protected function _prepareCollection()
    {
        parent::_prepareCollection();

        /** @var Mage_Reports_Model_Resource_Report_Collection $collection */
        $collection = $this->getCollection();
        $collection->initReport('sheep_subscription/report_renewal_schedule_collection');
    }


    protected function _prepareColumns()
    {
        $this->addColumn('subscription_id', array(
            'header' => Mage::helper('reports')->__('Subscription #'),
            'index'  => 'subscription_id',
        ));

        $this->addColumn('status', array(
            'header'  => Mage::helper('reports')->__('Status'),
            'index'   => 'status',
            'type'    => 'options',
            'options' => Mage::helper('sheep_subscription/renewal')->getStatusOptions()
        ));

        $this->addColumn('order_status', array(
            'header'  => Mage::helper('reports')->__('Order Status'),
            'index'   => 'order_status',
            'type'    => 'options',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses()
        ));

        $this->addColumn('subtotal', array(
            'header'   => Mage::helper('reports')->__('Subtotal'),
            'index'    => 'base_subtotal',
            'type'     => 'price',
            'currency' => 'base_currency_code',
        ));

        $this->addColumn('expected_grand_total', array(
            'header'   => Mage::helper('reports')->__('Expected Grand Total'),
            'index'    => 'base_grand_total',
            'type'     => 'price',
            'currency' => 'base_currency_code',
        ));

        $this->addColumn('order_grand_total', array(
            'header'   => Mage::helper('reports')->__('Order Grand Total'),
            'index'    => 'order_base_grand_total',
            'type'     => 'price',
            'currency' => 'base_currency_code',
        ));

        $this->addExportType('*/*/exportScheduledCsv', Mage::helper('reports')->__('CSV'));
        $this->addExportType('*/*/exportScheduledExcel', Mage::helper('reports')->__('Excel XML'));

        parent::_prepareColumns();
    }

}
