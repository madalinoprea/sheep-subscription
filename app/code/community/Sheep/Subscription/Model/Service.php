<?php

/**
 * Class Sheep_Subscription_Model_Service
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Model_Service
{
    // This needs to be a digit if max 10 characters to have Product.Options.reloadPrice working correctly
    const SUBSCRIPTION_PRODUCT_OPTION_ID = '9999999999';
    const SUBSCRIPTION_PRODUCT_OPTION_TYPE_VALUE_ID_PREFIX = 'pss_subscription_type_';


    /**
     * @return Sheep_Subscription_Model_Notification_Service
     */
    public function getNotificationService()
    {
        return Mage::getSingleton('sheep_subscription/notification_service');
    }

    /**
     * Dispatches a subscription related event
     *
     * @param string                                $eventName
     * @param Sheep_Subscription_Model_Subscription $subscription
     */
    public function dispatchEvent($eventName, Sheep_Subscription_Model_Subscription $subscription, Sheep_Subscription_Model_Renewal $renewal = null)
    {
        Mage::dispatchEvent($eventName, array('subscription' => $subscription, 'renewal' => $renewal));
    }


    /**
     * Responsible to set price and price_type on subscription types that will be passed to product option values
     *
     * @param Mage_Catalog_Model_Product                        $product
     * @param Sheep_Subscription_Model_Resource_Type_Collection $types
     */
    public function addProductPriceToType(Mage_Catalog_Model_Product $product, Sheep_Subscription_Model_Resource_Type_Collection $types)
    {
        $typePrices = Mage::helper('sheep_subscription/product')->getProductSubscriptionTypePrices($product);

        foreach ($types as $type) {
            // Do we have price overrides at product level
            $productTypePrice = $typePrices->getItemByColumnValue('type_id', $type->getId());

            if ($productTypePrice) {
                // Use overridden price set on product
                if ((float)$productTypePrice->getDiscount()) {
                    $type->setPrice(-1 * $productTypePrice->getDiscount());
                    $type->setPriceType('');
                } else if ((float)$productTypePrice->getDiscountPercent()) {
                    $type->setPrice(-1 * $productTypePrice->getDiscountPercent());
                    $type->setPriceType('percent');
                }
            } else {
                // Use default discount on type
                $type->setPriceType('percent');
                $type->setPrice(-1 * $type->getDiscount());
            }
        }
    }


    /**
     * Adds subscription option to a product
     *
     * @param Mage_Catalog_Model_Product $product
     */
    public function addSubscriptionOptions(Mage_Catalog_Model_Product $product)
    {
        /** @var Sheep_Subscription_Helper_Product $helper */
        $helper = Mage::helper('sheep_subscription/product');
        if (!$helper->isSubscriptionProduct($product)) {
            return;
        }

        /** @var Mage_Catalog_Model_Product_Option $option */
        $option = Mage::getModel('catalog/product_option');
        $option->setId(self::SUBSCRIPTION_PRODUCT_OPTION_ID);
        $option->setType(Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN);
        $option->setIsRequire($product->getPssIsSubscription() == Sheep_Subscription_Helper_Product::PRODUCT_SUBSCRIPTION_ONLY);
        $option->setTitle($helper->__('Subscription Type'));
        $option->setProduct($product);

        $subscriptionTypes = $helper->getProductSubscriptionTypes($product);
        $this->addProductPriceToType($product, $subscriptionTypes);

        /** @var Sheep_Subscription_Model_Type $subscriptionType */
        foreach ($subscriptionTypes as $subscriptionType) {

            /** @var Mage_Catalog_Model_Product_Option_Value $optionValue */
            $optionValue = Mage::getModel('catalog/product_option_value');
            $optionValue->setId(self::SUBSCRIPTION_PRODUCT_OPTION_TYPE_VALUE_ID_PREFIX . $subscriptionType->getId());
            $optionValue->setTitle($helper->__($subscriptionType->getTitle()));
            $optionValue->setOption($option);
            $optionValue->setProduct($product);

            $optionValue->setPriceType($subscriptionType->getPriceType());
            $optionValue->setPrice($subscriptionType->getPrice());

            $option->addValue($optionValue);
        }

        $product->setHasOptions(true);
        $product->addOption($option);
    }


    /**
     * Creates subscriptions for all order items
     *
     * @param Mage_Sales_Model_Order $order
     * @return array
     */
    public function createSubscriptionsFromOrder(Mage_Sales_Model_Order $order)
    {
        $subscriptions = array();
        /** @var Sheep_Subscription_Helper_Product $productHelper */
        $productHelper = Mage::helper('sheep_subscription/product');

        // array where key is subscription type id and value is an array of order items
        $subscriptionDefinitions = array();

        $orderItems = $order->getAllItems();
        /** @var Mage_Sales_Model_Order_Item $orderItem */
        foreach ($orderItems as $orderItem) {
            /** @var Mage_Catalog_Model_Product $orderItemProduct */
            $orderItemProduct = $orderItem->getProduct();

            // Is subscription product and was sold as a subscription
            $typeId = $this->getSubscriptionTypeId($orderItem);
            if ($typeId && $productHelper->isSubscriptionProduct($orderItemProduct)) {
                if (!array_key_exists($typeId, $subscriptionDefinitions)) {
                    $subscriptionDefinitions[$typeId] = array();
                }
                $subscriptionDefinitions[$typeId][] = $orderItem;
            }
        }

        // Lets create subscriptions
        foreach ($subscriptionDefinitions as $typeId => $orderItems) {
            $subscriptions[$typeId] = $this->createSubscription($typeId, $order, $orderItems);
        }

        return $subscriptions;
    }


    /**
     * Checks if we already defined a subscription for specified order and type
     *
     * @param int $orderId
     * @param int $typeId
     * @return bool
     */
    public function hasExistingSubscriptions($orderId, $typeId)
    {
        /** @var Sheep_Subscription_Model_Resource_Subscription_Collection $subscriptions */
        $subscriptions = Mage::getModel('sheep_subscription/subscription')->getCollection();
        $subscriptions->addOrderFilter($orderId);
        $subscriptions->addTypeFilter($typeId);

        return $subscriptions->getSize() > 0;
    }


    /**
     * Creates subscription for specified type containing all specified order items
     *
     * @param int                           $typeId
     * @param Mage_Sales_Model_Order        $order
     * @param Mage_Sales_Model_Order_Item[] $orderItems
     * @return Sheep_Subscription_Model_Subscription
     * @throws Exception
     */
    public function createSubscription($typeId, $order, $orderItems)
    {
        if ($this->hasExistingSubscriptions($order->getId(), $typeId)) {
            return null;
        }

        $subscriptionType = Mage::getModel('sheep_subscription/type')->load($typeId);
        if (!$subscriptionType->getId()) {
            return null;
        }

        $now = Mage::getSingleton('core/date')->gmtDate();

        $subscription = Mage::getModel('sheep_subscription/subscription');
        $subscriptionQuote = $this->_initSubscriptionQuote($order, $orderItems);

        $subscription->setTypeId($subscriptionType->getId());
        $subscription->setTypeInfo($subscriptionType->getData());
        $subscription->setQuoteId($subscriptionQuote->getId());
        $subscription->setCustomerId($order->getCustomerId());
        $subscription->setOriginalOrderId($order->getId());

        $subscription->setStartDate($now);
        $subscription->setStatus(Sheep_Subscription_Model_Subscription::STATUS_ACTIVE);
        $subscription->save();

        // Allow subscription payment to save additional info from order
        $subscription->getSubscriptionPayment()->onCreateSubscription($subscription, $order);

        $this->getNotificationService()->sendNewSubscriptionEmail($subscription);

        Mage::log("New subscription created order incrementId={$order->getIncrementId()} typeId={$typeId} subscriptionId={$subscription->getId()}");

        $this->dispatchEvent('pss_create_subscription', $subscription);

        return $subscription;
    }


    /**
     * Initiates subscription quote from order and specified order items
     *      - associated customer
     *      - billing address
     *      - shipping address
     *      - quote items with custom options
     *      - shipping method
     *      - payment method
     *
     * @param                               $order
     * @param Mage_Sales_Model_Order_Item[] $orderItems
     * @return Mage_Sales_Model_Quote
     */
    protected function _initSubscriptionQuote($order, array $orderItems)
    {
        /** @var Mage_Sales_Model_Convert_Order $orderConverter */
        $orderConverter = Mage::getModel('sales/convert_order');

        /** @var Mage_Sales_Model_Quote $subscriptionQuote */
        $subscriptionQuote = $orderConverter->toQuote($order);

        // Adds recurring products
        $this->_addOrderItems($subscriptionQuote, $orderItems);

        $subscriptionQuote->setBillingAddress($orderConverter->addressToQuoteAddress($order->getBillingAddress()));
        if ($order->getShippingAddress()) {
            $shippingAddress = $orderConverter->toQuoteShippingAddress($order);
            $shippingAddress->setQuote($subscriptionQuote);
            $shippingAddress->setShippingMethod($order->getShippingMethod());
            $shippingAddress->setCollectShippingRates(1);
            $subscriptionQuote->setShippingAddress($shippingAddress);
        }

        // Import payment info
        $payment = $orderConverter->paymentToQuotePayment($order->getPayment());
        $subscriptionQuote->addPayment($payment);

        // Set subscription quote attributes
        $subscriptionQuote->setCustomerFirstname($order->getCustomerFirstname());
        $subscriptionQuote->setCustomerLastname($order->getCustomerLastname());
        $subscriptionQuote->setPssIsSubscription(Sheep_Subscription_Model_Subscription::QUOTE_IS_SUBSCRIPTION_YES);
        $subscriptionQuote->setIsActive(0);
        // Update quote totals when is first time loaded
        $subscriptionQuote->setTriggerRecollect(1);
        $subscriptionQuote->save();

        return $subscriptionQuote;
    }


    /**
     * Adds specified order item to subscription quote by disabling inventory checks and salable flag
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param array                  $orderItems
     */
    protected function _addOrderItems(Mage_Sales_Model_Quote $quote, array $orderItems)
    {
        // Disable salable check to allow currently out of stock items to be added to our subscription quote
        Mage::helper('catalog/product')->setSkipSaleableCheck(true);

        // Disable inventory checks - we don't care for now if current inventory cannot be satisfied
        $quote->setIsSuperMode(true);

        /** @var Mage_Sales_Model_Order_Item $orderItem */
        foreach ($orderItems as $orderItem) {
            /** Configurable products are added correclty by reloading product like on @see \Mage_Checkout_Model_Cart::addOrderItem */
            $product = Mage::getModel('catalog/product')
                ->setStoreId($quote->getStoreId())
                ->load($orderItem->getProductId());

            // Import subscription item
            $quote->addProduct($product, $this->_getBuyRequest($orderItem));
        }

        // Restore saleable check
        Mage::helper('catalog/product')->setSkipSaleableCheck(false);
    }


    /**
     * Returns buy request for subscription item
     *
     * @param Mage_Sales_Model_Order_Item $orderItem
     * @return Varien_Object
     */
    protected function _getBuyRequest(Mage_Sales_Model_Order_Item $orderItem)
    {
        $buyRequest = new Varien_Object();
        $buyRequest->setQty($orderItem->getQtyOrdered());
        $buyRequestOptions = array();

        $productOptions = $orderItem->getProductOptions();
        if ($productOptions && array_key_exists('options', $productOptions)) {
            foreach ($productOptions['options'] as $optionData) {
                $buyRequestOptions[$optionData['option_id']] = $optionData['option_value'];
            }
        }

        if ($buyRequestOptions) {
            $buyRequest->setOptions($buyRequestOptions);
        }

        // Handle configurable buy request
        if (array_key_exists('super_attribute', $productOptions['info_buyRequest'])) {
            $buyRequest->setSuperAttribute($productOptions['info_buyRequest']['super_attribute']);
        }

        // Handle bundle buy request
        if (array_key_exists('bundle_option', $productOptions['info_buyRequest'])) {
            $buyRequest->setBundleOption($productOptions['info_buyRequest']['bundle_option']);
            $buyRequest->setBundleOptionQty($productOptions['info_buyRequest']['bundle_option_qty']);
        }

        // Handle downloadable buy request
        if (array_key_exists('links', $productOptions['info_buyRequest'])) {
            $buyRequest->setLinks($productOptions['info_buyRequest']['links']);
        }

        return $buyRequest;
    }


    /**
     * Returns subscription type associated to an order item by reading options from its
     * buy request
     *
     * @param Mage_Sales_Model_Order_Item $orderItem
     * @return int|null
     */
    public function getSubscriptionTypeId(Mage_Sales_Model_Order_Item $orderItem)
    {
        $subscriptionTypeId = null;
        $buyRequest = $orderItem->getProductOptionByCode('info_buyRequest');
        if ($buyRequest && array_key_exists('options', $buyRequest)) {
            foreach ($buyRequest['options'] as $optionCode => $optionValue) {
                if ($optionCode == self::SUBSCRIPTION_PRODUCT_OPTION_ID) {
                    $subscriptionTypeId = (int)str_replace(self::SUBSCRIPTION_PRODUCT_OPTION_TYPE_VALUE_ID_PREFIX, '', $optionValue);
                    break;
                }
            }
        }

        return $subscriptionTypeId;
    }


    /**
     * Returns pending renewal for subscription that was payed on specified date
     *
     * @param Sheep_Subscription_Model_Subscription $subscription
     * @param                                       $lastPaidDate
     * @return Sheep_Subscription_Model_Renewal
     * @throws Exception
     */
    public function getNextRenewal(Sheep_Subscription_Model_Subscription $subscription, $lastPaidDate)
    {
        $renewalDate = $subscription->getType()->getNextRenewalDate($lastPaidDate);

        /** @var Sheep_Subscription_Model_Renewal $renewal */
        $renewal = Mage::getModel('sheep_subscription/renewal');
        $renewal->setSubscriptionId($subscription->getId());
        $renewal->setStatus(Sheep_Subscription_Model_Renewal::STATUS_PENDING);
        $renewal->setDate($renewalDate);

        return $renewal;
    }


    /**
     * Returns pending renewals that can be scheduled for processing
     *
     * @param string $olderThanTime Datetime string using format Y-m-d H:i:s (in GMT)
     * @return Sheep_Subscription_Model_Resource_Renewal_Collection
     */
    public function getPendingRenewals($olderThanTime)
    {
        /** @var Sheep_Subscription_Model_Resource_Renewal_Collection $renewals */
        $renewals = Mage::getModel('sheep_subscription/renewal')->getCollection();
        $renewals->addStatusFilter(Sheep_Subscription_Model_Renewal::STATUS_PENDING);
        $renewals->addEarlierFilter($olderThanTime);

        return $renewals;
    }


    /**
     * Tries to create an order for specified renewal and to update schedule.
     *  - creates order for specified renewal
     *  - create new renewal
     *
     *  - TODO: handle finite subscriptions
     * Returns true if renewal was payed successfully.
     *
     * @param Sheep_Subscription_Model_Renewal $renewal
     * @return bool
     * @throws Exception
     */
    public function processRenewal(Sheep_Subscription_Model_Renewal $renewal)
    {
        /** @var Sheep_Subscription_Model_Subscription $subscription */
        $subscription = $renewal->getSubscription();
        if (!$subscription->isActive()) {
            throw new Exception("Associated subscription is not active subscriptionId={$renewal->getSubscriptionId()} subscriptionStatus={$subscription->getStatusLabel()}");
        }

        $order = null;
        $maxFailedPayments = Mage::helper('sheep_subscription/renewal')->getMaxFailedPayments();

        try {
            $order = $renewal->createOrder();

            // Update renewal
            $renewal->setStatus(Sheep_Subscription_Model_Renewal::STATUS_PAYED);
            $renewal->setOrderId($order->getId());
            $renewal->setLastMessage('');
            $renewal->save();

            $this->dispatchEvent('pss_renewal_processed', $subscription, $renewal);
        } catch (Exception $e) {
            // Store exception message
            $renewal->setLastMessage($e->getMessage());
            $renewal->setFailedPaymentsCount($renewal->getFailedPaymentsCount() + 1);

            // Send a subscription summary e-mail that will highlight unpaid renewals that have failed attempts

            // Mark renewal as failed if max attempts was reached
            if ($renewal->getFailedPaymentsCount() >= $maxFailedPayments) {
                $renewal->setStatus(Sheep_Subscription_Model_Renewal::STATUS_FAILED);
            }

            $this->dispatchEvent('pss_renewal_error', $subscription, $renewal);

            $renewal->save();
        }

        // Send new order e-mail
        if ($order && $order->getCanSendNewEmailFlag()) {
            try {
                $order->sendNewOrderEmail();
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }

        // If renewal was marked as failed, expire subscription
        if ($renewal->isFailed()) {
            $this->expireSubscription($subscription);
        }

        // create next renewal if renewal was payed
        if ($renewal->isPayed()) {
            $nextRenewal = $this->getNextRenewal($subscription, $renewal->getDate());
            $nextRenewal->save();
        }

        return $renewal->isPayed();
    }


    /**
     * Deletes pending renewal associated to specified subscription
     *
     * @param Sheep_Subscription_Model_Subscription $subscription
     * @throws Exception
     */
    protected function _deletePendingRenewals(Sheep_Subscription_Model_Subscription $subscription)
    {
        // Delete pending renewals
        $renewals = $subscription->getRelatedRenewals();
        $renewals->clear();
        $renewals->addStatusFilter(Sheep_Subscription_Model_Renewal::STATUS_PENDING);
        $renewals->walk('delete');

        // Check if they were deleted
        $renewals->clear();
        if ($renewals->getSize()) {
            throw new Exception('Unable to delete pending renewals for subscription ' . $subscription->getId());
        }
    }


    /**
     * Checks if subscription can be paused based on its status and its associated payment method
     *
     * @param Sheep_Subscription_Model_Subscription $subscription
     * @return bool
     */
    public function canBePaused(Sheep_Subscription_Model_Subscription $subscription)
    {
        return $subscription->getId() && $subscription->isActive();
    }


    /**
     * Pause specified subscription (change its status, remove any pending renewals)
     *
     * @param Sheep_Subscription_Model_Subscription $subscription
     * @throws Exception
     */
    public function pauseSubscription(Sheep_Subscription_Model_Subscription $subscription)
    {
        if (!$this->canBePaused($subscription)) {
            throw new Exception('Subscription cannot be paused.');
        }

        /** @var Magento_Db_Adapter_Pdo_Mysql $connection */
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        try {
            $connection->beginTransaction();

            // Delete pending renewals
            $this->_deletePendingRenewals($subscription);

            $subscription->setStatus(Sheep_Subscription_Model_Subscription::STATUS_PAUSED);
            $subscription->save();

            $connection->commit();
        } catch (Exception $e) {
            $connection->rollback();
            throw $e;
        }

        $this->dispatchEvent('pss_pause_subscription', $subscription);
    }


    /**
     * Checks if subscription can be resumed based on its status and its associated payment method
     *
     * @param Sheep_Subscription_Model_Subscription $subscription
     * @return bool
     */
    public function canBeResumed(Sheep_Subscription_Model_Subscription $subscription)
    {
        return $subscription->getId() && $subscription->isPaused();
    }


    /**
     * Resumes subscription: changes subscription status and creates pending renewal
     *
     * @param Sheep_Subscription_Model_Subscription $subscription
     * @throws Exception
     */
    public function resumeSubscription(Sheep_Subscription_Model_Subscription $subscription)
    {
        if (!$this->canBeResumed($subscription)) {
            throw new Exception('Subscription cannot be resumed.');
        }

        /** @var Magento_Db_Adapter_Pdo_Mysql $connection */
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');

        try {
            $connection->beginTransaction();

            // Update subscription status
            $subscription->setStatus(Sheep_Subscription_Model_Subscription::STATUS_ACTIVE);
            $subscription->save();

            // Update subscription schedule
            $now = Mage::getSingleton('core/date')->gmtDate();
            $renewal = $this->getNextRenewal($subscription, $now);

            $renewal->save();

            if (!$renewal->getId()) {
                throw new Exception('Unable to create subscription schedule');
            }

            $connection->commit();
        } catch (Exception $e) {
            $connection->rollback();
            throw $e;
        }

        $this->dispatchEvent('pss_resume_subscription', $subscription);
    }


    /**
     * Checks if subscription can be cancelled
     *
     * @param Sheep_Subscription_Model_Subscription $subscription
     * @return bool
     */
    public function canBeCancelled(Sheep_Subscription_Model_Subscription $subscription)
    {
        return $subscription->getId() && ($subscription->isActive() || $subscription->isPaused());
    }


    /**
     * Cancels specified subscription (changes its status, removes schedule)
     *
     * @param Sheep_Subscription_Model_Subscription $subscription
     * @throws Exception
     */
    public function cancelSubscription(Sheep_Subscription_Model_Subscription $subscription)
    {
        if (!$this->canBeCancelled($subscription)) {
            throw new Exception('Subscription cannot be cancelled.');
        }

        /** @var Magento_Db_Adapter_Pdo_Mysql $connection */
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');

        try {
            $connection->beginTransaction();

            // Update subscription status
            $now = Mage::getSingleton('core/date')->gmtDate();
            $subscription->setStatus(Sheep_Subscription_Model_Subscription::STATUS_CANCELLED);
            $subscription->setCanceledAt($now);
            $subscription->save();

            // Update subscription schedule
            $this->_deletePendingRenewals($subscription);

            $connection->commit();
        } catch (Exception $e) {
            $connection->rollback();
            throw $e;
        }

        $this->dispatchEvent('pss_cancel_subscription', $subscription);
    }


    /**
     * Checks if we can change subscription renewal date.
     *
     * This change is allowed only for active subscriptions and only if permitted by configuration.
     *
     * @param Sheep_Subscription_Model_Subscription $subscription
     * @return bool
     */
    public function canChangeRenewalDate(Sheep_Subscription_Model_Subscription $subscription)
    {
        return $subscription->getId() && $subscription->isActive() && Mage::helper('sheep_subscription')->getIsAccountRenewalEditAllowed();
    }


    /**
     * Update date for next active renewal for specified subscription
     *
     * @param Sheep_Subscription_Model_Subscription $subscription
     * @param                                       $dateString
     * @throws Exception
     */
    public function changeRenewalDate(Sheep_Subscription_Model_Subscription $subscription, $dateString)
    {
        if (!$this->canChangeRenewalDate($subscription)) {
            throw new Exception('Renewal date cannot be changed');
        }

        $renewal = $subscription->getNextRenewal();
        if (!$renewal->getId()) {
            throw new Exception('Next renewal cannot be found');
        }

        if ($renewal->getStatus() != Sheep_Subscription_Model_Renewal::STATUS_PENDING) {
            throw new Exception('Renewal cannot be changed in current state');
        }

        // Normalize date
        $dateTime = strtotime($dateString);
        if ($dateTime === false) {
            throw new Exception('Please specify a valid date format');
        }
        $dateString = date('Y-m-d H:i:s', $dateTime);

        // Validate date is in future
        if (strtotime($dateString) <= time()) {
            throw new Exception('Please specify a date in future');
        }

        $renewal->setDate($dateString);
        $renewal->save();

        $this->dispatchEvent('pss_change_renewal_date', $subscription);
    }


    /**
     * Expires specified subscription
     *
     * @param Sheep_Subscription_Model_Subscription $subscription
     * @throws Exception
     */
    public function expireSubscription(Sheep_Subscription_Model_Subscription $subscription)
    {
        $subscription->setStatus(Sheep_Subscription_Model_Subscription::STATUS_EXPIRED)->save();

        $this->dispatchEvent('pss_expire_subscription', $subscription);
    }


    /**
     * Checks if is allowed to change subscription shipping information (shipping address and shipping method)
     *
     * @param Sheep_Subscription_Model_Subscription $subscription
     * @return bool
     */
    public function canChangeShippingInformation(Sheep_Subscription_Model_Subscription $subscription)
    {
        return $subscription->getId()
        && ($subscription->isActive() || $subscription->isPaused())
        && !$subscription->getQuote()->isVirtual()
        && Mage::helper('sheep_subscription')->getIsAccountShippingEditAllowed();
    }


    /**
     * Sets customer save address as subscription shipping address
     *
     * @param Sheep_Subscription_Model_Subscription $subscription
     * @param                                       $customerAddressId
     * @return array|bool
     */
    public function setSubscriptionShippingAddress(Sheep_Subscription_Model_Subscription $subscription, $customerAddressId)
    {
        if (!$this->canChangeShippingInformation($subscription)) {
            return array('Shipping address cannot be changed on this subscription.');
        }

        /** @var Mage_Customer_Model_Address $customerAddress */
        $customerAddress = Mage::getModel('customer/address')->load($customerAddressId);
        if (!$customerAddress->getId() || $customerAddress->getCustomerId() != $subscription->getCustomerId()) {
            return array('Specified customer address was not found.');
        }

        /** @var Mage_Sales_Model_Quote_Address $address */
        $address = $subscription->getQuote()->getShippingAddress();
        $address->importCustomerAddress($customerAddress)->setSaveInAddressBook(0);

        /** @var Mage_Customer_Model_Form $addressForm */
        $addressForm = Mage::getModel('customer/form');
        $addressForm->setFormCode('customer_address_edit')->setEntityType('customer_address');

        // Validate
        $addressForm->setEntity($address);
        $addressErrors = $addressForm->validateData($address->getData());

        // Address looks correct
        if ($addressErrors !== true) {
            return $addressErrors;
        }

        $address->implodeStreetAddress();
        $address->setCollectShippingRates(true);

        $validateRes = $address->validate();
        if ($validateRes !== true) {
            return $validateRes;
        }

        // And save
        $subscription->getQuote()->collectTotals()->save();

        return array();
    }


    /**
     * Sets address data as subscription shipping address
     *
     * @param Sheep_Subscription_Model_Subscription $subscription
     * @param array                                 $postData
     * @return array|bool
     */
    public function setSubscriptionShippingAddressData(Sheep_Subscription_Model_Subscription $subscription, array $postData)
    {
        if (!$this->canChangeShippingInformation($subscription)) {
            return array('Shipping address cannot be changed on this subscription.');
        }

        /** @var Mage_Sales_Model_Quote_Address $address */
        $address = $subscription->getQuote()->getShippingAddress();

        /** @var Mage_Customer_Model_Form $addressForm */
        $addressForm = Mage::getModel('customer/form');
        $addressForm->setFormCode('customer_address_edit')->setEntityType('customer_address');
        $addressForm->setEntity($address);

        // emulate request object
        $addressData = $addressForm->extractData($addressForm->prepareRequest($postData));
        $addressErrors = $addressForm->validateData($addressData);

        // Address looks correct
        if ($addressErrors !== true) {
            return $addressErrors;
        }

        $addressForm->compactData($addressData);
        // unset shipping address attributes which were not shown in form
        foreach ($addressForm->getAttributes() as $attribute) {
            if (!isset($postData[$attribute->getAttributeCode()])) {
                $address->setData($attribute->getAttributeCode(), NULL);
            }
        }

        $address->setSaveInAddressBook(0);
        $address->setSameAsBilling(0);
        $address->setCustomerAddressId(null);
        $address->implodeStreetAddress();

        $address->setCollectShippingRates(true);

        $validateRes = $address->validate();
        if ($validateRes !== true) {
            return $validateRes;
        }

        // And save
        $subscription->getQuote()->collectTotals()->save();

        return array();
    }


    /**
     * @param Sheep_Subscription_Model_Subscription $subscription
     * @param string                                $shippingMethod
     * @return array
     */
    public function setSubscriptionShippingMethod(Sheep_Subscription_Model_Subscription $subscription, $shippingMethod)
    {
        if (!$this->canChangeShippingInformation($subscription)) {
            return array('Shipping information cannot be changed on this subscription.');
        }

        $quote = $subscription->getQuote();
        $rate = $quote->getShippingAddress()->getShippingRateByCode($shippingMethod);
        if (!$rate) {
            return array('Invalid shipping method.');
        }
        $quote->getShippingAddress()->setShippingMethod($shippingMethod);

        $quote->collectTotals()->save();

        return array();
    }


    /**
     * Checks if we allow payment information change for current subscription
     *
     * Basically we don't see any reason to do this.
     *
     * @param Sheep_Subscription_Model_Subscription $subscription
     * @return bool
     */
    public function canChangePaymentInformation(Sheep_Subscription_Model_Subscription $subscription)
    {
        return $subscription->getId()
        && ($subscription->isActive() || $subscription->isPaused() || $subscription->isCancelled() || $subscription->isExpired())
        && Mage::helper('sheep_subscription')->getIsAccountPaymentEditAllowed();
    }


    /**
     * Adds subscription's products to current cart
     *
     * @param Sheep_Subscription_Model_Subscription $subscription
     * @throws Exception
     */
    public function addSubscriptionToCart(Sheep_Subscription_Model_Subscription $subscription)
    {
        if (!$this->canChangePaymentInformation($subscription)) {
            throw new Exception('Subscription payment information change is not allowed.');
        }

        $checkoutSession = Mage::getSingleton('checkout/session');
        /** @var Mage_Sales_Model_Quote $quote */
        $quote = $checkoutSession->getQuote();

        /** @var Mage_Sales_Model_Quote $subscriptionQuote */
        $subscriptionQuote = $subscription->getQuote();

        /** @var Mage_Sales_Model_Quote_Item $item */
        foreach ($subscriptionQuote->getAllVisibleItems() as $item) {
            $quote->addProduct($item->getProduct(), $item->getBuyRequest());
        }

        $quote->setTriggerRecollect(1);
        $quote->save();
        $checkoutSession->setQuoteId($quote->getId());
    }
}
