<?php

/**
 * Class Sheep_Subscription_Model_Type
 *
 * @method int getId()
 * @method Sheep_Subscription_Model_Type setId(int $value)
 * @method string getTitle()
 * @method Sheep_Subscription_Model_Type setTitle(string $value)
 * @method int getStatus()
 * @method Sheep_Subscription_Model_Type setStatus(int $value)
 * @method int getPeriodCount()
 * @method Sheep_Subscription_Model_Type setPeriodCount(int $value)
 * @method string getPeriodUnit()
 * @method Sheep_Subscription_Model_Type setPeriodUnit(string $value)
 * @method int getIsInfinite()
 * @method Sheep_Subscription_Model_Type setIsInfinite(int $value)
 * @method int getOccurrences()
 * @method Sheep_Subscription_Model_Type setOccurrences(int $value)
 * @method int getHasTrial()
 * @method Sheep_Subscription_Model_Type setHasTrial(int $value)
 * @method int getTrialOccurrences()
 * @method Sheep_Subscription_Model_Type setTrialOccurrences(int $value)
 * @method float getInitialFee()
 * @method Sheep_Subscription_Model_Type setInitialFee(float $value)
 * @method float getDiscount()
 * @method Sheep_Subscription_Model_Type setDiscount(float $value)
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Model_Type extends Mage_Core_Model_Abstract
{
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    const PERIOD_UNIT_DAYS = 'days';
    const PERIOD_UNIT_WEEKS = 'weeks';
    const PERIOD_UNIT_MONTHS = 'months';
    const PERIOD_UNIT_YEARS = 'years';

    const IS_FINITE = 0;
    const IS_INFINITE = 1;

    const WITHOUT_TRIAL = 0;
    const HAS_TRIAL = 1;

    protected $_eventPrefix = 'ss_type';

    protected function _construct()
    {
        $this->_init('sheep_subscription/type');
    }

    public function _beforeSave()
    {
        $this->validate();
        return parent::_beforeSave();
    }

    /**
     * Checks data integrity for subscription type
     *
     * @throws Exception
     */
    public function validate()
    {
        if ($this->getIsInfinite() == self::IS_FINITE && !(int)$this->getOccurrences()) {
            throw new Exception('Number of occurrences needs to be specified for finite subscription types.');
        }

        if ($this->getHasTrial() == self::HAS_TRIAL && !(int)$this->getTrialOccurrences()) {
            throw new Exception('Number of trial occurrences needs to be specified for subscription types with trial');
        }

        return true;
    }

    /**
     * Returns default values for subscription type model
     * @return array
     */
    public function getDefaultValues()
    {
        return array(
            'status' => self::STATUS_ENABLED,
            'period_count' => 1,
            'is_infinite' => self::IS_INFINITE,
            'has_trial' => self::WITHOUT_TRIAL
        );
    }


    /**
     * TODO: how do we handle dates
     * @param $lastPayedDate
     * @return string
     * @throws Exception
     */
    public function getNextRenewalDate($lastPayedDate)
    {
        $renewalTime = $lastPayedDate;
        $periods = $this->getPeriodCount();
        $renewalTime .= " +{$periods} {$this->getPeriodUnit()}";
        $renewalTimestamp = strtotime($renewalTime);

        if ($renewalTimestamp===false) {
            throw new Exception('Unable to compute next renewal date ' . $renewalTime);
        }

        return date('Y-m-d H:i:s', $renewalTimestamp);
    }

}
