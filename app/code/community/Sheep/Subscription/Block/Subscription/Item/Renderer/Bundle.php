<?php

/**
 * Class Sheep_Subscription_Block_Subscription_Item_Renderer_Bundle
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Block_Subscription_Item_Renderer_Bundle
    extends Mage_Bundle_Block_Checkout_Cart_Item_Renderer
    implements Sheep_Subscription_Block_Subscription_Item_Renderer_Interface
{
    public function canEdit()
    {
        return false;
    }

}
