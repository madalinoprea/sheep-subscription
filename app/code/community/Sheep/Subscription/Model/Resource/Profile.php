<?php

/**
 * Class Sheep_Subscription_Model_Resource_Profile
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Model_Resource_Profile extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('sheep_subscription/profile', 'customer_id');
        $this->_isPkAutoIncrement = false;
    }

}
