<?php

/**
 * Class Sheep_Subscription_Block_Adminhtml_Renewal prepares renewal grid container in admin
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Block_Adminhtml_Renewal extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    /**
     * Sets up renewal admin container (removes add button)
     */
    public function __construct()
    {
        $this->_blockGroup = 'sheep_subscription';
        $this->_controller = 'adminhtml_renewal';
        $this->_headerText = $this->__('Subscription Renewals');
        parent::__construct();

        $this->removeButton('add');
    }

}

