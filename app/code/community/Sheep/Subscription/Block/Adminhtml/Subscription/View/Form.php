<?php

/**
 * Class Sheep_Subscription_Block_Adminhtml_Subscription_View_Form
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Block_Adminhtml_Subscription_View_Form extends Mage_Adminhtml_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        // Creates the id for tabs
        $this->setTemplate('sheep_subscription/subscription/view/form.phtml');
    }
}
