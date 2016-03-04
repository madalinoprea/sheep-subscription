<?php

/**
 * Class Sheep_Subscription_Model_Profile
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @method int getCustomerId()
 * @method Sheep_Subscription_Model_Profile setCustomerId(int $value)
 * @method string getNotifiedAt()
 * @method Sheep_Subscription_Model_Profile setNotifiedAt(string $value)
 */
class Sheep_Subscription_Model_Profile extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        $this->_init('sheep_subscription/profile');
    }

}
