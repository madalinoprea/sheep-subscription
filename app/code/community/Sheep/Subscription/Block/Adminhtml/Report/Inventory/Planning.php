<?php

/**
 * Class Sheep_Subscription_Block_Adminhtml_Report_Inventory_Planning
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2016, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Block_Adminhtml_Report_Inventory_Planning extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'sheep_subscription';
        $this->_controller = 'adminhtml_report_inventory_planning';
        $this->_headerText = Mage::helper('sheep_subscription')->__('Inventory Planning');
        parent::__construct();
        $this->_removeButton('add');
    }
}
