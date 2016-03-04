<?php

/**
 * Class Sheep_Subscription_Block_Adminhtml_Customer_Edit_Tab_Subscriptions
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Block_Adminhtml_Customer_Edit_Tab_Subscriptions extends Sheep_Subscription_Block_Adminhtml_Subscription_Grid
{
    protected $customerId;

    /**
     * @return mixed
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * @param mixed $customerId
     */
    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;
    }

    /**
     * Prepares grid collection
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $customerId = $this->getCustomerId();
        /** @var Sheep_Subscription_Model_Resource_Subscription_Collection $collection */
        $collection = Mage::getModel('sheep_subscription/subscription')->getCollection();
        $collection->addQuoteData(array('quote_currency_code', 'customer_email', 'subtotal'));
        $collection->addCustomerFilter($customerId);

        $this->setCollection($collection);

        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        parent::_prepareColumns();
        $this->removeColumn('customer');

        return $this;
    }

    /**
     * Returns grid url that can be used to retrieve grid via ajax
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/customerTab', array('_current' => true));
    }

}
