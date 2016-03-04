<?php

/**
 * Class Sheep_Subscription_Block_Adminhtml_Subscription_Items
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Block_Adminhtml_Subscription_Items extends Mage_Adminhtml_Block_Sales_Items_Abstract
{
    /**
     * @return Sheep_Subscription_Model_Subscription
     */
    public function getSubscription()
    {
        return Mage::registry('pss_subscription');
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('sheep_subscription/subscription/items.phtml');
        $this->addItemRender('default', 'sheep_subscription/adminhtml_subscription_items_renderer_default', 'sheep_subscription/subscription/items/renderer/default.phtml');
    }

    public function getItemsCollection()
    {
        return $this->getSubscription()->getItemsCollection();
    }

    public function canEditQty()
    {
        return false;
    }

}
