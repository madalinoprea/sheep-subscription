<?php

/**
 * Class Sheep_Subscription_Block_Adminhtml_Report_Subscription_Canceled_Grid
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2016, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Block_Adminhtml_Report_Subscription_Canceled_Grid extends Mage_Adminhtml_Block_Report_Grid
{

    public function __construct()
    {
        parent::__construct();

        // Show all subscriptions created for that period
        $this->_subReportSize = 0;

        $this->setId('gridCanceledSubscriptions');
    }


    protected function _prepareCollection()
    {
        parent::_prepareCollection();

        /** @var Mage_Reports_Model_Resource_Report_Collection $collection */
        $collection = $this->getCollection();
        $collection->initReport('sheep_subscription/report_subscription_canceled_collection');
    }


    protected function _prepareColumns()
    {
        $helper = Mage::helper('sheep_subscription/subscription');

        $this->addColumn('subscription_id', array(
            'header' => $this->__('Subscription Id'),
            'index' => 'id',
        ));

        $this->addColumn('status', array(
            'header' => $this->__('Status'),
            'index' => 'status',
            'type' => 'options',
            'options' => $helper->getStatusOptions()
        ));

        $this->addColumn('created_at', array(
            'header' => $this->__('Created At'),
            'index' => 'created_at',
            'type' => 'datetime'
        ));

        $this->addColumn('base_subtotal', array(
            'header' => $this->__('Subtotal'),
            'index' => 'base_subtotal',
            'type' => 'price',
            'currency' => 'base_currency_code',
            'total' => 'sum',
        ));

        $this->addColumn('base_grand_total', array(
            'header' => $this->__('Grand Total'),
            'index' => 'base_grand_total',
            'type' => 'price',
            'currency' => 'base_currency_code',
            'total' => 'sum',
        ));

        $this->addExportType('*/*/exportCanceledCsv', $this->__('CSV'));
        $this->addExportType('*/*/exportCanceledExcel', $this->__('Excel XML'));

        parent::_prepareColumns();
    }

}
