<?php

/**
 * Class Sheep_Subscription_Model_Subscription
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @method int getId()
 * @method Sheep_Subscription_Model_Subscription setId(int $value)
 * @method int getCustomerId()
 * @method Sheep_Subscription_Model_Subscription setCustomerId(int $value)
 * @method int getTypeId()
 * @method Sheep_Subscription_Model_Subscription setTypeId(int $value)
 * @method string getStartDate()
 * @method Sheep_Subscription_Model_Subscription setStartDate(string $value)
 * @method string getStatus()
 * @method Sheep_Subscription_Model_Subscription setQuoteId(int $value)
 * @method int getQuoteId()
 * @method Sheep_Subscription_Model_Subscription setStatus(string $value)
 * @method string getCreatedAt()
 * @method Sheep_Subscription_Model_Subscription setCreatedAt(string $value)
 * @method string getUpdatedAt()
 * @method Sheep_Subscription_Model_Subscription setUpdatedAt(string $value)
 * @method string getCanceledAt()
 * @method Sheep_Subscription_Model_Subscription setCanceledAt(string $value)
 * @method int getOriginalOrderId()
 * @method Sheep_Subscription_Model_Subscription setOriginalOrderId(int $value)
 *
 */
class Sheep_Subscription_Model_Subscription extends Mage_Core_Model_Abstract
{
    // Status constants
    const STATUS_ACTIVE = 10;
    const STATUS_PAUSED = 20;
    const STATUS_CANCELLED = 30;
    const STATUS_EXPIRED = 50;

    const QUOTE_IS_SUBSCRIPTION_YES = 1;
    const QUOTE_IS_SUBSCRIPTION_NO = 0;
    const QUOTE_HAS_SUBSCRIPTIONS_YES = 1;
    const QUOTE_HAS_SUBSCRIPTIONS_NO = 0;

    protected $_eventPrefix = 'ss_subscription';

    /** @var Mage_Sales_Model_Quote $quote */
    protected $quote;
    /** @var  array $typeInfo */
    protected $typeInfo;
    /** @var Sheep_Subscription_Model_Type $type */
    protected $type;
    /** @var  Sheep_Subscription_Model_Resource_Renewal_Collection */
    protected $relatedRenewals;
    /** @var Mage_Customer_Model_Customer $customer */
    protected $customer;
    /** @var Sheep_Subscription_Model_Renewal $nextRenewal */
    protected $nextRenewal;
    protected $shortDescription;

    /**
     * Sets resource model
     */
    protected function _construct()
    {
        $this->_init('sheep_subscription/subscription');
    }

    /**
     * Updates created_at and update_at
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
        $now = Mage::getSingleton('core/date')->gmtDate();
        if ($this->isObjectNew()) {
            $this->setCreatedAt($now);
        }
        $this->setUpdatedAt($now);

        return parent::_beforeSave();
    }

    /**
     * Checks if subscription is active
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->getStatus() == self::STATUS_ACTIVE;
    }

    /**
     * Checks if subscription is cancelled
     *
     * @return bool
     */
    public function isCancelled()
    {
        return $this->getStatus() == self::STATUS_CANCELLED;
    }

    /**
     * Checks if subscription is paused
     *
     * @return bool
     */
    public function isPaused()
    {
        return $this->getStatus() == self::STATUS_PAUSED;
    }


    /**
     * Check if subscription is expired
     *
     * @return bool
     */
    public function isExpired()
    {
        return $this->getStatus() == self::STATUS_EXPIRED;
    }


    /**
     * Get renewal date with current store timezone
     *
     * @return Zend_Date
     */
    public function getStartDateStoreDate()
    {
        return Mage::app()->getLocale()->storeDate(
            null,
            Varien_Date::toTimestamp($this->getStartDate()),
            true
        );
    }

    /**
     * Get created_at with current store timezone
     *
     * @return Zend_Date
     */
    public function getCreatedAtStoreDate()
    {
        return Mage::app()->getLocale()->storeDate(
            null,
            Varien_Date::toTimestamp($this->getCreatedAt()),
            true
        );
    }

    /**
     * Sets type info
     *
     * @param array $typeData
     * @return mixed
     */
    public function setTypeInfo(array $typeData)
    {
        unset($typeData['id']);
        parent::setTypeInfo(Mage::helper('core')->jsonEncode($typeData));
        $this->typeInfo = null;

        return $this;
    }

    /**
     * Returns subscription type info
     *
     * @return array
     */
    public function getTypeInfo()
    {
        if ($this->typeInfo === null) {
            $this->typeInfo = array();
            if ($typeInfoJsonString = parent::getTypeInfo()) {
                $this->typeInfo = Mage::helper('core')->jsonDecode($typeInfoJsonString);
            }
        }

        return $this->typeInfo;
    }

    /**
     * Returns a subscription type based on type_info stored when subscription was created.
     * type_id only references type used, but type data might have been affected and might not
     * look as when customer purchased the subscription.
     *
     * @return Sheep_Subscription_Model_Type|null
     */
    public function getType()
    {
        if ($this->type === null) {
            $this->type = Mage::getModel('sheep_subscription/type');
            $this->type->setData($this->getTypeInfo());
        }

        return $this->type;
    }

    public function getShortDescription()
    {
        if ($this->shortDescription === null) {

            $productNames = array();
            foreach ($this->getQuote()->getAllVisibleItems() as $quoteItem) {
                $productNames[] = $quoteItem->getName();
            }

            $this->shortDescription = substr(join(', ', $productNames), 0, 255);
        }

        return $this->shortDescription;
    }


    /**
     * Returns quote associated to subscription
     *
     * @return Mage_Sales_Model_Quote|null
     */
    public function getQuote()
    {
        if ($this->quote === null) {
            $this->quote = Mage::getModel('sales/quote');
            if ($this->getQuoteId()) {
                $this->quote->loadByIdWithoutStore($this->getQuoteId());
            }
        }

        return $this->quote;
    }

    /**
     * Returns associated customer
     *
     * @return Mage_Core_Model_Abstract|Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        if ($this->customer === null) {
            $this->customer = Mage::getModel('customer/customer')->load($this->getCustomerId());
        }

        return $this->customer;
    }

    /**
     * Returns subscription status label
     *
     * @return string
     */
    public function getStatusLabel()
    {
        $helper = Mage::helper('sheep_subscription/subscription');
        $statusLabels = $helper->getStatusOptions();

        if (array_key_exists($this->getStatus(), $statusLabels)) {
            return $statusLabels[$this->getStatus()];
        } else {
            return $helper->__('N/A');
        }
    }

    /**
     * Returns subscription billing address
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    public function getBillingAddress()
    {
        return $this->getQuote()->getBillingAddress();
    }

    /**
     * Checks if subscription is virtual
     *
     * @return bool
     */
    public function getIsVirtual()
    {
        return $this->getQuote()->getIsVirtual();
    }

    /**
     * Returns subscription shipping address
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    public function getShippingAddress()
    {
        return $this->getQuote()->getShippingAddress();
    }

    /**
     * Returns subscription quote payment method
     *
     * @return Mage_Sales_Model_Quote_Payment
     */
    public function getPayment()
    {
        return $this->getQuote()->getPayment();
    }

    /**
     * @return Sheep_Subscription_Model_Payment_Interface
     * @throws Exception
     */
    public function getSubscriptionPayment()
    {
        return Mage::helper('sheep_subscription/payment')->getSubscriptionPaymentMethodModel($this->getPayment()->getMethod());
    }


    /**
     * Returns info of subscription payment. Every payment method is responsible to populate this info
     * based on its needs
     *
     * @return array
     */
    public function getSubscriptionPaymentInfo()
    {
        return Mage::getModel('sheep_subscription/payment')->load($this->getId(), 'subscription_id')->getInfo();
    }

    /**
     * Returns subscription items
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function getItemsCollection()
    {
        return $this->getQuote()->getItemsCollection();
    }


    /**
     * Returns renewals associated to this subscription
     *
     * @return Sheep_Subscription_Model_Resource_Renewal_Collection
     */
    public function getRelatedRenewals()
    {
        if ($this->relatedRenewals === null) {
            $this->relatedRenewals = Mage::getModel('sheep_subscription/renewal')->getCollection();
            $this->relatedRenewals->addSubscriptionFilter($this->getId());
            $this->relatedRenewals->addRenewalOrderData(array('order_increment_id' => 'increment_id', 'order_status' => 'status'));
        }

        return $this->relatedRenewals;
    }


    /**
     * Returns subscription next renewal.
     *
     * Warning: If you are working with a list of subscription is preferable to add renewal date using something like @see \Sheep_Subscription_Model_Resource_Subscription_Collection::addNextRenewalDate
     *
     * @return Sheep_Subscription_Model_Renewal
     */
    public function getNextRenewal()
    {
        if ($this->nextRenewal === null) {
            /** @var Sheep_Subscription_Model_Resource_Renewal_Collection $renewals */
            $renewals = Mage::getModel('sheep_subscription/renewal')->getCollection();
            $renewals->addSubscriptionFilter($this->getId());
            $renewals->addStatusFilter(array('in' => array(Sheep_Subscription_Model_Renewal::STATUS_PENDING, Sheep_Subscription_Model_Renewal::STATUS_PROCESSING, Sheep_Subscription_Model_Renewal::STATUS_WAITING)));
            $renewals->setCurPage(1)->setPageSize(1);

            $this->nextRenewal = $renewals->getFirstItem();
        }

        return $this->nextRenewal;
    }

}
