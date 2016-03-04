<?php
/**
 * File ${FILE_NAME}
 * 
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
  
class Sheep_Subscription_Model_Resource_ProductTypePrice extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('sheep_subscription/product_subscription_type_price', 'id');
    }

}
