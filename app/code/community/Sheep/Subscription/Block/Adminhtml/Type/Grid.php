<?php

/**
 * Class Sheep_Subscription_Block_Adminhtml_Type_Grid builds subscription type grid
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Block_Adminhtml_Type_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('grid_id');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('sheep_subscription/type')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        /** @var Sheep_Subscription_Helper_Type $helper */
        $helper = Mage::helper('sheep_subscription/type');
        $this->addColumn('title',
            array(
                'header' => $this->__('Title'),
                'width'  => '300px',
                'index'  => 'title'
            )
        );
        $this->addColumn('status',
            array(
                'header'  => $this->__('Status'),
                'type'    => 'options',
                'width'   => '80px',
                'index'   => 'status',
                'options' => $helper->getStatusOptions()
            )
        );
        $this->addColumn('period_count',
            array(
                'header' => $this->__('Period Count'),
                'index'  => 'period_count',
                'type'   => 'number',
                'width'  => '50px'
            )
        );
        $this->addColumn('period_unit',
            array(
                'header'  => $this->__('Period Unit'),
                'index'   => 'period_unit',
                'type'    => 'options',
                'options' => $helper->getPeriodUnitOptions(),
                'width'   => '50px'
            )
        );
        $this->addColumn('discount',
            array(
                'header' => $this->__('Discount Percentage'),
                'index'  => 'discount',
                'type'   => 'number',
                'width'  => '50px'
            ));
        $this->addColumn('is_infinite',
            array(
                'header'  => $this->__('Is Infinite'),
                'index'   => 'is_infinite',
                'type'    => 'options',
                'options' => $helper->getIsInfiniteOptions(),
                'width'   => '50px'
            )
        );
        $this->addColumn('occurrences',
            array(
                'header' => $this->__('Occurrences'),
                'index'  => 'occurrences',
                'type'   => 'number',
            )
        );
        $this->addColumn('has_trial',
            array(
                'header'  => $this->__('Has Trial'),
                'index'   => 'has_trial',
                'type'    => 'options',
                'options' => $helper->getHasTrialOptions(),
                'width'   => '50px'
            )
        );
        $this->addColumn('trial_occurrences',
            array(
                'header' => $this->__('Trial Occurrences'),
                'index'  => 'trial_occurrences',
                'type'   => 'number',
                'width'  => '50px'
            )
        );
        $this->addColumn('initial_fee',
            array(
                'header' => $this->__('Initial Fee'),
                'index'  => 'initial_fee',
                'type'   => 'price'
            )
        );

        $this->addExportType('*/*/exportCsv', $this->__('CSV'));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    protected function _prepareMassaction()
    {
        if (!Mage::getSingleton('sheep_subscription/adminhtml_acl')->canEditSubscriptionTypes()) {
            return $this;
        }

        $modelPk = Mage::getModel('sheep_subscription/type')->getResource()->getIdFieldName();
        $this->setMassactionIdField($modelPk);
        $this->getMassactionBlock()->setFormFieldName('ids');
        $this->getMassactionBlock()->setUseSelectAll(false);
        $this->getMassactionBlock()->addItem('delete', array(
            'label' => $this->__('Delete'),
            'url'   => $this->getUrl('*/*/massDelete'),
        ));
        return $this;
    }
}
