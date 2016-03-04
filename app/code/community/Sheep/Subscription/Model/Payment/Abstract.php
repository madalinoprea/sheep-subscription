<?php
/**
 * Class Sheep_Subscription_Model_Payment_Abstract
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */

abstract class Sheep_Subscription_Model_Payment_Abstract implements Sheep_Subscription_Model_Payment_Interface
{
    /** @var Mage_Payment_Model_Method_Abstract $payment */
    protected $payment;

    public function setPayment(Mage_Payment_Model_Method_Abstract $payment)
    {
        $this->payment = $payment;
    }

    public function getTitle()
    {
        return $this->payment->getTitle();
    }

    public function getCode()
    {
        return $this->payment->getCode();
    }

}
