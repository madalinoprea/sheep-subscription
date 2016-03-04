<?php

/**
 * Class Sheep_Subscription_Block_Subscription_View
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Block_Subscription_Items extends Mage_Sales_Block_Items_Abstract
{
    /** @var Sheep_Subscription_Model_Subscription */
    protected $subscription;


    /**
     * Initialize default item renderer
     */
    public function _construct()
    {
        parent::_construct();
        $this->addItemRender('default', 'sheep_subscription/subscription_item_renderer', 'sheep_subscription/subscription/item/default.phtml');
        $this->addItemRender('configurable', 'sheep_subscription/subscription_item_renderer_configurable', 'sheep_subscription/subscription/item/default.phtml');
        $this->addItemRender('bundle', 'sheep_subscription/subscription_item_renderer_bundle', 'sheep_subscription/subscription/item/default.phtml');
        $this->addItemRender('downloadable', 'sheep_subscription/subscription_item_renderer_downloadable', 'sheep_subscription/subscription/item/default.phtml');
    }


    /**
     * Returns current subscription
     * @return Sheep_Subscription_Model_Subscription
     */
    public function getSubscription()
    {
        if ($this->subscription === null) {
            $this->subscription = Mage::registry('pss_subscription');

            if ($this->subscription===null) {
                $this->subscription = $this->getData('subscription');
            }
        }

        return $this->subscription;
    }
}
