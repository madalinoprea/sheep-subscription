<?php
/**
 * Class Sheep_Subscription_Model_Resource_Renewal
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */ 
class Sheep_Subscription_Model_Resource_Renewal extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('sheep_subscription/renewal', 'id');
    }

}
