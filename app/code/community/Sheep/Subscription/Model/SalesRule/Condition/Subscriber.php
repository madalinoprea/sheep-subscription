<?php

/**
 * Class Sheep_Subscription_Model_SalesRule_Condition_Subscriber
 *
 * A customer can have these subscriber states:
 *      - active subscriber (has current active subscriptions)
 *      - former subscriber (has subscriptions that are not active, some might be active)
 *      - non subscriber
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2016, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Model_SalesRule_Condition_Subscriber extends Sheep_Subscription_Model_SalesRule_Condition_Base
{
    const SUBSCRIBER_TYPE_ACTIVE = 1;
    const SUBSCRIBER_TYPE_FORMER = 2;
    const SUBSCRIBER_TYPE_NON = 3;

    const CONDITION_ATTRIBUTE_IS_SUBSCRIBER = 'pss_subscriber';


    /**
     * @return array
     */
    public function getSubscriberTypeOptionArray()
    {
        /** @var Sheep_Subscription_Helper_Data $helper */
        $helper = Mage::helper('sheep_subscription');

        return array(
            array('value' => self::SUBSCRIBER_TYPE_ACTIVE, 'label' => $helper->__('Active Subscriber')),
            array('value' => self::SUBSCRIBER_TYPE_FORMER, 'label' => $helper->__('Former Subscriber')),
            array('value' => self::SUBSCRIBER_TYPE_NON, 'label' => $helper->__('Non Subscriber'))
        );
    }


    /**
     * Attributes related to a subscriber
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $attributes = array(self::CONDITION_ATTRIBUTE_IS_SUBSCRIBER => 'Subscriber Type');
        $this->setAttributeOption($attributes);

        return $this;
    }


    /**
     * A single option is allowed to be selected
     *
     * @return string
     */
    public function getInputType()
    {
        return 'select';
    }


    /**
     * Options are rendered as select
     *
     * @return string
     */
    public function getValueElementType()
    {
        return 'select';
    }


    /**
     * Value options for our attributes
     *
     * @return mixed
     */
    public function getValueSelectOptions()
    {
        if (!$this->hasData('value_select_options')) {
            $this->setData('value_select_options', $this->getSubscriberTypeOptionArray());
        }

        return $this->getData('value_select_options');
    }


    /**
     * $object can be Mage_Sales_Model_Quote_Address
     *
     * @param Varien_Object $object
     * @return bool
     */
    public function validate(Varien_Object $object)
    {
        /** @var Sheep_Subscription_Helper_Subscription $helper */
        $helper = Mage::helper('sheep_subscription/subscription');
        $customerId = $this->getCustomerId($object);

        if (!$customerId) {
            return false;
        }

        /** @var Sheep_Subscription_Model_Resource_Subscription_Collection $subscriptions */
        $subscriptions = $helper->getCustomerSubscriptions($customerId);

        // Validate differently based on selected option
        switch ($this->getValueParsed()) {
            case self::SUBSCRIBER_TYPE_ACTIVE:
                $subscriptions->addStatusFilter(Sheep_Subscription_Model_Subscription::STATUS_ACTIVE);
                $result = $subscriptions->getSize() > 0;
                break;
            case self::SUBSCRIBER_TYPE_FORMER:
                $subscriptions->addStatusFilter(array('neq' => Sheep_Subscription_Model_Subscription::STATUS_ACTIVE));
                $result = $subscriptions->getSize() > 0;
                break;
            case self::SUBSCRIBER_TYPE_NON:
                $result = $subscriptions->getSize() == 0;
                break;
            default:
                return false;
        }

        return $this->getOperatorForValidate() == '==' ? $result : !$result;
    }

}
