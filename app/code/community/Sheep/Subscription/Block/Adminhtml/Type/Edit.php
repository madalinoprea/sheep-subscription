<?php

/**
 * Class Sheep_Subscription_Block_Adminhtml_Type_Edit
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Block_Adminhtml_Type_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->_blockGroup = 'sheep_subscription';
        $this->_controller = 'adminhtml_type';
        $modelTitle = $this->getModelTitle();

        if (!Mage::getSingleton('sheep_subscription/adminhtml_acl')->canEditSubscriptionTypes()) {
            $this->removeButton('save');
            $this->removeButton('delete');
            return;
        }

        $this->_updateButton('save', 'label', $this->helper('sheep_subscription')->__("Save $modelTitle"));
        $this->_addButton('saveandcontinue', array(
            'label'   => $this->helper('sheep_subscription')->__('Save and Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class'   => 'save',
        ), -100);

        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getModel()
    {
        return Mage::registry('current_subscription_type');
    }

    public function getModelTitle()
    {
        return 'Subscription Type';
    }

    public function getHeaderText()
    {
        $model = $this->getModel();
        $modelTitle = $this->getModelTitle();

        return ($model && $model->getId()) ?
            $this->helper('sheep_subscription')->__("Edit $modelTitle (ID: {$model->getId()})") :
            $this->helper('sheep_subscription')->__("New $modelTitle");
    }

    /**
     * Get URL for back (reset) button
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/index');
    }

    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/delete', array($this->_objectId => $this->getRequest()->getParam($this->_objectId)));
    }

    /**
     * Get form save URL
     *
     * @deprecated
     * @see getFormActionUrl()
     * @return string
     */
    public function getSaveUrl()
    {
        $this->setData('form_action_url', 'save');
        return $this->getFormActionUrl();
    }

}
