<?php

/**
 * Class Sheep_Subscription_Block_Adminhtml_Subscription_Grid
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Block_Adminhtml_Subscription_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    /**
     * Prepares grid default parameters
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('subscription_grid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(false);
        $this->setUseAjax(true);
    }

    /**
     * Prepares grid collection
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        /** @var Sheep_Subscription_Model_Resource_Subscription_Collection $collection */
        $collection = Mage::getModel('sheep_subscription/subscription')->getCollection();
        $collection->addQuoteData(array('quote_currency_code', 'customer_email', 'subtotal'));

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepares grid columns
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        /** @var Sheep_Subscription_Helper_Subscription $helper */
        $helper = Mage::helper('sheep_subscription/subscription');

        $this->addColumn('id',
            array(
                'header' => $this->__('Subscription #'),
                'index'  => 'id',
                'width'  => '50px'
            )
        );
        $this->addColumn('customer',
            array(
                'header'                    => $this->__('Customer E-mail'),
                'width'                     => '50px',
                'index'                     => 'customer_email',
                'frame_callback'            => array($this, 'customerPageUrl'),
                'filter_condition_callback' => array($this, 'customerEmailFilter'),
            )
        );
        $this->addColumn('status',
            array(
                'header'  => $this->__('Status'),
                'width'   => '50px',
                'index'   => 'status',
                'type'    => 'options',
                'options' => $helper->getStatusOptions()
            )
        );
        $this->addColumn('type',
            array(
                'header'         => $this->__('Type'),
                'width'          => '300px',
                'index'          => 'type_info',
                'frame_callback' => array($this, 'typeInfo'),
            )
        );
        $this->addColumn('subtotal',
            array(
                'header'                    => $this->__('Subtotal'),
                'width'                     => '70px',
                'type'                      => 'price',
                'index'                     => 'subtotal',
                'currency'                  => 'quote_currency_code',
                'filter_condition_callback' => array($this, 'quoteSubtotalFilter'),
            )
        );

        $this->addColumn('start_date',
            array(
                'header' => $this->__('Start Date'),
                'width'  => '100px',
                'index'  => 'start_date',
                'type'   => 'datetime'
            )
        );

        $this->addColumn('created_at',
            array(
                'header' => $this->__('Created At'),
                'width'  => '100px',
                'index'  => 'created_at',
                'type'   => 'datetime'
            )
        );


        $this->addExportType('*/*/exportCsv', $this->__('CSV'));

        return parent::_prepareColumns();
    }

    /**
     * Returns subscription edit url
     *
     * @param $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return Mage::getSingleton('sheep_subscription/adminhtml_acl')->canViewSubscriptionDetails() ?
            $this->getUrl('*/*/view', array('subscription_id' => $row->getId())) : '#';
    }


    /**
     * Returns grid url that can be used to retrieve grid via ajax
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

    /**
     * Grid url
     *
     * @param array $params
     * @return string
     */
    public function getAbsoluteGridUrl($params = array())
    {
        return $this->getUrl('*/*/grid', $params);
    }

    /**
     * @param $renderedValue
     * @param Sheep_Subscription_Model_Subscription $row
     * @return string
     */
    public function typeInfo($renderedValue, Sheep_Subscription_Model_Subscription $row)
    {
        if (!$renderedValue){
            return '';
        }

        $typeUrl = $row->getTypeId() ? $this->getUrl('adminhtml/subscriptionType/edit', array('id' => $row->getTypeId())) : '#';
        $typeTitle = $this->escapeHtml($row->getType()->getTitle());

        return "<a href='{$typeUrl}'>{$typeTitle}</a>";
    }

    /**
     * @param $renderedValue
     * @param Sheep_Subscription_Model_Subscription $row
     * @return string
     */
    public function customerPageUrl($renderedValue, Sheep_Subscription_Model_Subscription $row)
    {
        $customerEmail = $this->escapeHtml($renderedValue);
        $customerPageUrl = $this->getUrl('adminhtml/customer/edit', array('id' => $row->getCustomerId()));

        return "<a href='{$customerPageUrl}' target='_blank'>$customerEmail</a>";
    }

    /**
     * @param Sheep_Subscription_Model_Resource_Subscription_Collection $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @return $this
     */
    public function customerEmailFilter(Sheep_Subscription_Model_Resource_Subscription_Collection $collection, Mage_Adminhtml_Block_Widget_Grid_Column $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }
        $collection->addCustomerEmailFilter($value);

        return $this;
    }

    /**
     * @param Sheep_Subscription_Model_Resource_Subscription_Collection $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @return $this
     */
    public function quoteSubtotalFilter(Sheep_Subscription_Model_Resource_Subscription_Collection $collection, Mage_Adminhtml_Block_Widget_Grid_Column $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }
        if (array_key_exists('from', $value)) {
            $collection->addQuoteSubtotalFilter($value['from']);
        }
        if (array_key_exists('to', $value)) {
            $collection->addQuoteSubtotalFilter(null, $value['to']);
        }

        return $this;
    }

}
