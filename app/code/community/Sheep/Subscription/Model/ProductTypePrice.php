<?php

/**
 * Class Sheep_Subscription_Model_ProductTypePrice
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @method int getProductId()
 * @method Sheep_Subscription_Model_ProductTypePrice setProductId(int $value)
 * @method int getTypeId()
 * @method Sheep_Subscription_Model_ProductTypePrice setTypeId(int $value)
 * @method float getDiscountPercent()
 * @method Sheep_Subscription_Model_ProductTypePrice setDiscountPercent(float $value)
 * @method float getDiscount()
 * @method Sheep_Subscription_Model_ProductTypePrice setDiscount(float $value)
 */
class Sheep_Subscription_Model_ProductTypePrice extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        $this->_init('sheep_subscription/productTypePrice');
    }

}
