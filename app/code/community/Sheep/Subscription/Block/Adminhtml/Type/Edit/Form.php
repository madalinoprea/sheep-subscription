<?php

/**
 * Class Sheep_Subscription_Block_Adminhtml_Type_Edit_Form builds subscription type form for admin
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Block_Adminhtml_Type_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Returns current subscription type model
     * @return Sheep_Subscription_Model_Type
     */
    public function getModel()
    {
        return Mage::registry('current_subscription_type');
    }

    /**
     * Returns block title
     * @return string
     */
    public function getModelTitle()
    {
        return 'Subscription Type';
    }

    /**
     * Adds form elements
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        /** @var Sheep_Subscription_Helper_Type $helper */
        $helper = Mage::helper('sheep_subscription/type');
        $model = $this->getModel();
        $modelTitle = $this->getModelTitle();
        $form = new Varien_Data_Form(array(
            'id'     => 'edit_form',
            'action' => $this->getUrl('*/*/save'),
            'method' => 'post'
        ));

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend' => $helper->__("$modelTitle Information"),
        ));

        if ($model && $model->getId()) {
            $modelPk = $model->getResource()->getIdFieldName();
            $fieldset->addField($modelPk, 'hidden', array(
                'name' => $modelPk,
            ));
        }

        $fieldset->addField('title', 'text', array(
            'name'     => 'title',
            'label'    => $helper->__('Title'),
            'title'    => $helper->__('Tooltip text here'),
            'required' => true,
        ));
        $fieldset->addField('status', 'select', array(
            'name'     => 'status',
            'label'    => $helper->__('Status'),
            'title'    => $helper->__('Tooltip text here'),
            'options'  => $helper->getStatusOptions(),
            'required' => true,
            'value'    => Sheep_Subscription_Model_Type::STATUS_ENABLED
        ));
        $fieldset->addField('period_count', 'text', array(
            'name'     => 'period_count',
            'label'    => $helper->__('Period Count'),
            'required' => true
        ));
        $fieldset->addField('period_unit', 'select', array(
            'name'     => 'period_unit',
            'label'    => $helper->__('Period Unit'),
            'options'  => $helper->getPeriodUnitOptions(),
            'required' => true
        ));
        $fieldset->addField('discount', 'text', array(
            'name' => 'discount',
            'label' => $helper->__('Default Discount'),
            'required' => false,
        ));
        $isInfiniteField = $fieldset->addField('is_infinite', 'select', array(
            'name'     => 'is_infinite',
            'label'    => $helper->__('Is Infinite'),
            'options'  => $helper->getIsInfiniteOptions(),
            'required' => true,
        ));
        $occurrencesField = $fieldset->addField('occurrences', 'text', array(
            'name'     => 'occurrences',
            'label'    => $this->__('Occurrences'),
            'required' => true,
        ));
        $hasTrialField = $fieldset->addField('has_trial', 'select', array(
            'name'     => 'has_trial',
            'label'    => $helper->__('Has Trial'),
            'options'  => $helper->getHasTrialOptions(),
            'required' => true,
        ));
        $trialOccurrencesField = $fieldset->addField('trial_occurrences', 'text', array(
            'name'     => 'trial_occurrences',
            'label'    => $this->__('Trial Occurrences'),
            'required' => true,
        ));
        $fieldset->addType('price', 'Mage_Adminhtml_Block_Catalog_Product_Helper_Form_Price');
        $fieldset->addField('initial_fee', 'price', array(
            'name'     => 'initial_fee',
            'label'    => $this->__('Initial Fee'),
            'required' => false,
        ));

        if ($model) {
            $form->setValues($model->getId() ? $model->getData() : $model->getDefaultValues());
        }

        /** @var Mage_Adminhtml_Block_Widget_Form_Element_Dependence $dependenceField */
        $dependenceField = $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence');
        $dependenceField
            ->addFieldMap($isInfiniteField->getHtmlId(), $isInfiniteField->getName())
            ->addFieldMap($occurrencesField->getHtmlId(), $occurrencesField->getName())
            ->addFieldMap($hasTrialField->getHtmlId(), $hasTrialField->getName())
            ->addFieldMap($trialOccurrencesField->getHtmlId(), $trialOccurrencesField->getName())
            ->addFieldDependence(
                $occurrencesField->getName(),
                $isInfiniteField->getName(),
                Sheep_Subscription_Model_Type::IS_FINITE)
            ->addFieldDependence(
                $trialOccurrencesField->getName(),
                $hasTrialField->getName(),
                Sheep_Subscription_Model_Type::HAS_TRIAL
            );;
        $this->setChild('form_after', $dependenceField);
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

}
