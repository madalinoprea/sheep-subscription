<?php

/**
 * Subscription view tabs
 *
 * @category Sheep
 * @package  Sheep_Queue
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Block_Adminhtml_Subscription_View_Tabs
    extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Initialize Tabs
     * @access public
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('sheep_subscription_view_tabs');
        $this->setDestElementId('sheep_subscription_view');
        $this->setTitle(Mage::helper('sheep_subscription/subscription')->__('Subscription View'));
    }

    /**
     * before render html
     * @access protected
     * @return Sheep_Queue_Block_Adminhtml_Message_Edit_Tabs
     */
    protected function _beforeToHtml()
    {
        /** @var Sheep_Subscription_Model_Adminhtml_Acl $acl */
        $acl = Mage::getSingleton('sheep_subscription/adminhtml_acl');

        $helper = Mage::helper('sheep_subscription/subscription');

        if ($acl->canViewSubscriptionDetails()) {
            $this->addTab('subscription_info', array(
                'label' => $helper->__('Information'),
                'title' => $helper->__('Information'),
                'content' => $this->getLayout()->createBlock('sheep_subscription/adminhtml_subscription_view_tabs_info')->toHtml(),
            ));
        }

        if ($acl->canViewSubscriptionRenewals()) {
            $this->addTab('subscription_renewals', array(
                'label' => $helper->__('Renewals'),
                'title' => $helper->__('Renewals'),
                'class' => 'ajax',
                'url'   => $this->getUrl('adminhtml/subscription/renewalsTab', array('_current' => true)),
            ));
        }

        return parent::_beforeToHtml();
    }
}
