<?php

/**
 * Class Sheep_Subscription_Block_Adminhtml_Subscription_View_Tabs_Renewals
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Block_Adminhtml_Subscription_View_Tabs_Renewals
    extends Sheep_Subscription_Block_Adminhtml_Renewal_Grid
{
    /** @var int $subscriptionId */
    protected $subscriptionId;

    public function __construct()
    {
        parent::__construct();
        $this->setId('subscription_renewal_grid');
    }

    /**
     * @return int
     */
    public function getSubscriptionId()
    {
        return $this->subscriptionId;
    }

    /**
     * Sets subscription id for which we list the renewals
     *
     * @param int $subscriptionId
     */
    public function setSubscriptionId($subscriptionId)
    {
        $this->subscriptionId = $subscriptionId;
    }

    /**
     * Adds subscription id filter to list only renewals associated to configured subscription
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        /** @var Sheep_Subscription_Model_Resource_Renewal_Collection $collection */
        $collection = Mage::getModel('sheep_subscription/renewal')->getCollection();
        $collection->addSubscriptionFilter($this->getSubscriptionId());
        $collection->addSubscriptionData(array('quote_id', 'start_date'));
        $collection->addSubscriptionQuoteData(array('quote_currency_code', 'customer_email', 'subtotal'));
        $collection->addFilterToMap('status', 'main_table.status');
        $collection->addFilterToMap('id', 'main_table.id');

        $this->setCollection($collection);

        // bypass parent _prepareCollection
        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }

    /**
     * Removes subscription_id and customer_email column that don't make sense when
     * we list renewals associated to a subscription
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();
        $this->removeColumn('subscription_id');
        $this->removeColumn('customer_email');

        return $this;
    }

    /**
     * Grid url getter
     *
     * @return string current grid url
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/renewalsTab', array('_current' => true));
    }

}
