<?php
/**
 * Class Sheep_Subscription_Model_ProductType
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @method int getId()
 * @method Sheep_Subscription_Model_ProductType setId(int $value)
 * @method int getProductId()
 * @method Sheep_Subscription_Model_ProductType setProductId(int $value)
 * @method int getTypeId()
 * @method Sheep_Subscription_Model_ProductType setTypeId(int $value)
 */
class Sheep_Subscription_Model_ProductType extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        $this->_init('sheep_subscription/productType');
    }

}
