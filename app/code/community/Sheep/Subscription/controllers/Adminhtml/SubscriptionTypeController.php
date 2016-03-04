<?php

/**
 * Class Sheep_Subscription_Adminhtml_SubscriptionTypeController adds subscription type admin actions
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Adminhtml_SubscriptionTypeController extends Sheep_Subscription_Controller_Adminhtml_Action
{
    protected function _isAllowed()
    {
        $acl = $this->getAcl();

        return
            $acl->canViewSubscriptionTypes() ||
            $acl->canEditSubscriptionTypes();
    }

    public function indexAction()
    {
        $this->loadLayout();
        $this->_addContent($this->getLayout()->createBlock('sheep_subscription/adminhtml_type'));
        $this->renderLayout();
    }

    public function exportCsvAction()
    {
        $fileName = 'Subscription_Type_Export.csv';
        $content = $this->getLayout()->createBlock('sheep_subscription/adminhtml_type_grid')->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function massDeleteAction()
    {
        $ids = $this->getRequest()->getParam('ids');
        if (!is_array($ids)) {
            $this->_getSession()->addError($this->__('Please select Subscription Type(s).'));
            $this->_redirect('*/*/index');
        }

        try {
            /** @var Sheep_Subscription_Model_Resource_Type_Collection $subscriptionTypes */
            $subscriptionTypes = Mage::getModel('sheep_subscription/type')->getCollection();
            $subscriptionTypes->addFieldToFilter('id', $ids);
            $subscriptionTypes->walk('delete');

            $this->_getSession()->addSuccess(
                $this->__('Total of %d record(s) have been deleted.', count($ids))
            );
        } catch (Exception $e) {
            $this->_getSession()->addError(
                Mage::helper('sheep_subscription')->__('An error occurred while mass deleting items. Please review log and try again.')
            );
            Mage::logException($e);
        }

        $this->_redirect('*/*/index');
    }

    public function editAction()
    {
        if (!$this->getAcl()->canViewSubscriptionTypes()) {
            return $this->forwardDenied();
        }

        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('sheep_subscription/type');

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->_getSession()->addError(
                    Mage::helper('sheep_subscription')->__('This Subscription Type no longer exists.')
                );
                return $this->_redirect('*/*/');
            }
        }

        $data = $this->_getSession()->getFormData(true);
        if ($data) {
            $model->setData($data);
        }

        Mage::register('current_subscription_type', $model);

        $this->loadLayout();
        $this->_addContent($this->getLayout()->createBlock('sheep_subscription/adminhtml_type_edit'));
        $this->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function saveAction()
    {
        if (!$this->getAcl()->canEditSubscriptionTypes()) {
            return $this->forwardDenied();
        }

        $redirectBack = $this->getRequest()->getParam('back', false);
        if ($data = $this->getRequest()->getPost()) {

            $id = $this->getRequest()->getParam('id');
            $model = Mage::getModel('sheep_subscription/type');
            if ($id) {
                $model->load($id);
                if (!$model->getId()) {
                    $this->_getSession()->addError(
                        Mage::helper('sheep_subscription')->__('This Subscription Type no longer exists.')
                    );
                    return $this->_redirect('*/*/index');
                }
            }

            // save model
            try {
                $model->addData($data);
                $this->_getSession()->setFormData($data);
                $model->save();
                $this->_getSession()->setFormData(false);
                $this->_getSession()->addSuccess(
                    Mage::helper('sheep_subscription')->__('The Subscription Type has been saved.')
                );
            } catch (Exception $e) {
                $this->_getSession()->addError(Mage::helper('sheep_subscription')->__('Unable to save the Subscription Type.'));
                $redirectBack = true;
                Mage::logException($e);
            }

            if ($redirectBack) {
                return $this->_redirect('*/*/edit', array('id' => $model->getId()));
            }
        }
        $this->_redirect('*/*/index');
    }

    public function deleteAction()
    {
        if (!$this->getAcl()->canEditSubscriptionTypes()) {
            return $this->forwardDenied();
        }

        if ($id = $this->getRequest()->getParam('id')) {
            try {
                // init model and delete
                $model = Mage::getModel('sheep_subscription/type');
                $model->load($id);
                if (!$model->getId()) {
                    Mage::throwException(Mage::helper('sheep_subscription')->__('Unable to find a Subscription Type to delete.'));
                }
                $model->delete();
                // display success message
                $this->_getSession()->addSuccess(
                    Mage::helper('sheep_subscription')->__('The Subscription Type has been deleted.')
                );
                // go to grid
                return $this->_redirect('*/*/index');
            } catch (Exception $e) {
                $this->_getSession()->addError(
                    Mage::helper('sheep_subscription')->__('An error occurred while deleting Subscription Type data. Please review log and try again.')
                );
                Mage::logException($e);
            }
            // redirect to edit form
            return $this->_redirect('*/*/edit', array('id' => $id));
        }

        $this->_getSession()->addError(
            Mage::helper('sheep_subscription')->__('Unable to find a Subscription Type to delete.')
        );
        $this->_redirect('*/*/index');
    }
}
