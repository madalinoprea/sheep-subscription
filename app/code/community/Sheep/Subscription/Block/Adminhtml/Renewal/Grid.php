<?php

/**
 * Class Sheep_Subscription_Block_Adminhtml_Renewal_Grid builds renewal grid in admin
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Block_Adminhtml_Renewal_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    /**
     * Initialize renewal grid: default sort order is by date DESC
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('renewal_grid');
        $this->setDefaultSort('date');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * Initialize grid collection: adds required subscription and subscription quote information that will be
     * displayed in the grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        /** @var Sheep_Subscription_Model_Resource_Renewal_Collection $collection */
        $collection = Mage::getModel('sheep_subscription/renewal')->getCollection();
        $collection->addSubscriptionData(array('quote_id', 'start_date'));
        $collection->addSubscriptionQuoteData(array('quote_currency_code', 'customer_email', 'subtotal'));
        $collection->addFilterToMap('id', 'main_table.id');
        $collection->addFilterToMap('status', 'main_table.status');

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        /** @var Sheep_Subscription_Helper_Renewal $helper */
        $helper = Mage::helper('sheep_subscription/renewal');

        $this->addColumn('id',
            array(
                'header' => $this->__('Renewal #'),
                'index'  => 'id',
                'width'  => '50px',
            )
        );
        $this->addColumn('date',
            array(
                'header' => $this->__('Renewal Date'),
                'width'  => '100px',
                'index'  => 'date',
                'type'   => 'datetime'
            )
        );
        $this->addColumn('customer_email',
            array(
                'header' => $this->__('Customer E-mail'),
                'width'  => '200px',
                'index'  => 'customer_email',
            )
        );
        $this->addColumn('subscription_id',
            array(
                'header'         => $this->__('Subscription #'),
                'width'          => '50px',
                'index'          => 'subscription_id',
                'frame_callback' => array($this, 'subscriptionUrl'),
            )
        );
        $this->addColumn('subtotal',
            array(
                'header'    => $this->__('Subtotal'),
                'width'     => '50px',
                'type'      => 'price',
                'index'     => 'subtotal',
                'currency'  => 'quote_currency_code',
                'filter' => false,
            )
        );
        $this->addColumn('order',
            array(
                'header'         => $this->__('Order'),
                'width'          => '50px',
                'index'          => 'order_id',
                'frame_callback' => array($this, 'orderUrl'),
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

        $this->addExportType('*/*/exportCsv', $this->__('CSV'));


        return parent::_prepareColumns();
    }

    /**
     * Disable renewal editing
     *
     * @param $row
     * @return bool
     */
    public function getRowUrl($row)
    {
        return false;
    }

    /**
     * Grid url getter
     *
     * @return string current grid url
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid');
    }

    /**
     * Returns url in admin for renewal's subscription
     *
     * @param $renderedValue
     * @param Sheep_Subscription_Model_Renewal $row
     * @return string
     */
    public function subscriptionUrl($renderedValue, Sheep_Subscription_Model_Renewal $row)
    {
        if (!$renderedValue) {
            return '';
        }

        $subscriptionUrl = $this->getUrl('adminhtml/subscription/view', array('subscription_id' => $row->getSubscriptionId()));
        return "<a href='{$subscriptionUrl}' target='_blank'>{$renderedValue}</a>";
    }

    /**
     * Returns url in admin for renewal order
     *
     * @param $renderedValue
     * @param Sheep_Subscription_Model_Renewal $row
     * @return string
     */
    public function orderUrl($renderedValue, Sheep_Subscription_Model_Renewal $row)
    {
        if (!$renderedValue) {
            return '';
        }

        $orderUrl = $this->getUrl('adminhtml/sales_order/view', array('order_id' => $row->getOrderId()));
        return "<a href='{$orderUrl}' target='_blank'>{$renderedValue}</a>";
    }
}
