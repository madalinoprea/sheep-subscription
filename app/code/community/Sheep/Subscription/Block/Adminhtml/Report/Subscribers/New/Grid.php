<?php

/**
 * Class Sheep_Subscription_Block_Adminhtml_Report_Subscribers_New_Grid
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2016, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Block_Adminhtml_Report_Subscribers_New_Grid extends Mage_Adminhtml_Block_Report_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('gridNewSubscribers');
    }


    protected function _prepareCollection()
    {
        parent::_prepareCollection();

        /** @var Mage_Reports_Model_Resource_Report_Collection $collection */
        $collection = $this->getCollection();
        $collection->initReport('sheep_subscription/report_subscriber_new_collection');
    }


    protected function _prepareColumns()
    {
        $this->addColumn('email', array(
            'header' => Mage::helper('reports')->__('New Subscribers'),
            'index' => 'email',
        ));

        $this->addExportType('*/*/exportNewCsv', Mage::helper('reports')->__('CSV'));
        $this->addExportType('*/*/exportNewExcel', Mage::helper('reports')->__('Excel XML'));

        parent::_prepareColumns();
    }

}
