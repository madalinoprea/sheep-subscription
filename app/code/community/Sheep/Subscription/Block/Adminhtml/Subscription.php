<?php

/**
 * Class Sheep_Subscription_Block_Adminhtml_Subscription
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Block_Adminhtml_Subscription extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    /**
     * Initialise subscription grid container
     */
    public function __construct()
    {
        $this->_blockGroup = 'sheep_subscription';
        $this->_controller = 'adminhtml_subscription';
         $this->_headerText      = $this->__('Purchased Subscriptions');
        parent::__construct();
        $this->removeButton('add');
    }

}
