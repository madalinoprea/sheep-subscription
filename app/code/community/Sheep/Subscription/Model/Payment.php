<?php
/**
 * Model that holds additional payment information associated to a subscription
 *
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @method int getId()
 * @method Sheep_Subscription_Model_Payment setId(int $value)
 * @method int getSubscriptionId()
 * @method Sheep_Subscription_Model_Payment setSubscriptionId(int $value)
 * @method string getExpirationDate()
 * @method Sheep_Subscription_Model_Payment setExpirationDate(string $value)
 */
class Sheep_Subscription_Model_Payment extends Mage_Core_Model_Abstract
{

    /** @var array */
    protected $infoData;


    protected function _construct()
    {
        $this->_init('sheep_subscription/payment');
    }


    public function setInfo(array $info)
    {
        parent::setInfo(Mage::helper('core')->jsonEncode($info));
        $this->infoData = null;
    }


    public function getInfo()
    {
        if ($this->infoData===null) {
            $this->infoData = array();
            if ($infoDataJsonString = parent::getInfo()) {
                $this->infoData = Mage::helper('core')->jsonDecode($infoDataJsonString);
            }
        }

        return $this->infoData;
    }

}
