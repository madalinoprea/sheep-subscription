<?php

/**
 * Class Sheep_Subscription_Block_Adminhtml_Subscription_View
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Block_Adminhtml_Subscription_View extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Initialize subscription edit container
     */
    public function __construct()
    {
        $this->_blockGroup = 'sheep_subscription';
        $this->_controller = 'adminhtml_subscription';
        $this->_mode = 'view';

        parent::__construct();
        $this->setId('sheep_subscription_view');

        // Adds subscription management buttons
        $this->addButtons();

        // Removes default buttons
        $this->removeButton('save');
        $this->removeButton('reset');
        $this->removeButton('delete');
    }


    /**
     * Adds subscription management operations
     */
    public function addButtons()
    {
        if (!Mage::getSingleton('sheep_subscription/adminhtml_acl')->canEditSubscription()) {
            return;
        }

        /** @var Sheep_Subscription_Model_Subscription $subscription */
        $subscription = $this->_getModel();
        /** @var Sheep_Subscription_Model_Service $service */
        $service = Mage::getSingleton('sheep_subscription/service');

        if ($service->canBeCancelled($subscription)) {
            $this->addButton('cancel', array(
                'label' => 'Cancel',
                'onclick' => "window.setLocation('{$this->getCancelUrl()}')"
            ));
        }

        if ($service->canBePaused($subscription)) {
            $this->addButton('pause', array(
                'label' => $this->__('Pause'),
                'onclick' => "window.setLocation('{$this->getPauseUrl()}')"
            ));
        }

        if ($service->canBeResumed($subscription)) {
            $this->addButton('resume', array(
                'label' => 'Resume',
                'onclick' => "window.setLocation('{$this->getResumeUrl()}')"
            ));
        }
    }


    protected function _getModel()
    {
        return Mage::registry('pss_subscription');
    }

    /**
     * Returns edit container header text
     *
     * @return mixed
     */
    public function getHeaderText()
    {
        return $this->__('View Subscription (ID: %s)', $this->_getModel()->getId());
    }

    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/index');
    }

    /**
     * Disable subscription delete
     * @return null
     */
    public function getDeleteUrl()
    {
        return null;
    }


    public function getPauseUrl()
    {
        return $this->getUrl('*/*/pause', array('subscription_id' => $this->_getModel()->getId()));
    }

    public function getCancelUrl()
    {
        return $this->getUrl('*/*/cancel', array('subscription_id' => $this->_getModel()->getId()));
    }


    public function getResumeUrl()
    {
        return $this->getUrl('*/*/resume', array('subscription_id' => $this->_getModel()->getId()));
    }

}
