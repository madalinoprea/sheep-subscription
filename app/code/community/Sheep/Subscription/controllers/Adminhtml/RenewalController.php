<?php

/**
 * Class Sheep_Subscription_Adminhtml_RenewalController adds renewal admin actions
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Adminhtml_RenewalController extends Sheep_Subscription_Controller_Adminhtml_Action
{

    /**
     * Checks if current session has access to these actions
     *
     * @return mixed
     */
    protected function _isAllowed()
    {
        return $this->getAcl()->canViewRenewals();
    }


    /**
     * Renewal list action
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_addContent($this->getLayout()->createBlock('sheep_subscription/adminhtml_renewal'));
        $this->_title('Subscriptions')->_title('Renewals');
        $this->renderLayout();
    }


    /**
     * Renewal export csv action
     */
    public function exportCsvAction()
    {
        $fileName = 'Renewal_export.csv';
        /** @var Sheep_Subscription_Block_Adminhtml_Renewal_Grid $block */
        $block = $this->getLayout()->createBlock('sheep_subscription/adminhtml_renewal_grid');
        $this->_prepareDownloadResponse($fileName, $block->getCsv());
    }


    /**
     * Renewal grid action
     */
    public function gridAction()
    {
        /** @var Sheep_Subscription_Block_Adminhtml_Renewal_Grid $block */
        $block = $this->getLayout()->createBlock('sheep_subscription/adminhtml_renewal_grid');
        $this->getResponse()->setBody($block->toHtml());
    }

}
