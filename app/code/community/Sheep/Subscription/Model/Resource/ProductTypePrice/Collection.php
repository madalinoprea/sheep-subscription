<?php

/**
 * Class Sheep_Subscription_Model_Resource_ProductTypePrice_Collection
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Model_Resource_ProductTypePrice_Collection extends Sheep_Subscription_Model_Resource_ProductType_Collection
{

    protected function _construct()
    {
        $this->_init('sheep_subscription/productTypePrice');
    }

}
