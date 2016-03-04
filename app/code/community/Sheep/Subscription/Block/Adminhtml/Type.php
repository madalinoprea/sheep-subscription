<?php

/**
 * Class Sheep_Subscription_Block_Adminhtml_Type builds grid container for subscription types
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Block_Adminhtml_Type extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
        $this->_blockGroup = 'sheep_subscription';
        $this->_controller = 'adminhtml_type';
        $this->_headerText = $this->__('Subscription Types');
        $this->_addButtonLabel = $this->__('Add Subscription Type');
        parent::__construct();

        if (!Mage::getSingleton('sheep_subscription/adminhtml_acl')->canEditSubscriptionTypes()) {
            $this->removeButton('add');
        }
    }

    public function getCreateUrl()
    {
        return $this->getUrl('*/*/new');
    }

}

