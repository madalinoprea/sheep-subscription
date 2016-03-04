<?php

/**
 * Class Sheep_Subscription_Model_Observer
 * TODO: Refactor this class - we're going to end up with too many methods that are not grouped by
 * business functionality
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Model_Observer
{

    /**
     * @return Sheep_Subscription_Model_Service
     */
    public function getService()
    {
        return Mage::getModel('sheep_subscription/service');
    }


    /**
     * Listens to catalog_controller_product_init and adds subscription options
     *
     * @param Varien_Event_Observer $observer
     */
    public function addSubscriptionOptions(Varien_Event_Observer $observer)
    {
        /** @var Mage_Catalog_Model_Product $product */
        $product = $observer->getEvent()->getProduct();
        $this->getService()->addSubscriptionOptions($product);
    }


    /**
     * Listens to sales_quote_item_collection_products_after_load and adds subscription options to
     * loaded products
     *
     * @param Varien_Event_Observer $observer
     */
    public function addSubscriptionOptionsOnProductCollection(Varien_Event_Observer $observer)
    {
        /** @var Mage_Catalog_Model_Resource_Product_Collection $collection */
        $collection = $observer->getEvent()->getProductCollection();

        /** @var Sheep_Subscription_Model_Service $subscriptionService */
        $subscriptionService = $this->getService();

        foreach ($collection as $product) {
            $subscriptionService->addSubscriptionOptions($product);
        }
    }


    /**
     * Listens to sales_model_service_quote_submit_after and creates subscription from order that was just created
     *
     * @param Varien_Event_Observer $observer
     */
    public function createSubscription(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Quote $quote */
        $quote = $observer->getEvent()->getQuote();
        /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getEvent()->getOrder();

        // Ignore subscription quotes that are renewed; we don't need to create another subscription
        if ($quote->getPssIsSubscription() == Sheep_Subscription_Model_Subscription::QUOTE_IS_SUBSCRIPTION_YES) {
            return;
        }

        $helper = Mage::helper('sheep_subscription');

        try {
            $subscriptions = $this->getService()->createSubscriptionsFromOrder($order);

            if ($subscriptions) {
                $message = count($subscriptions) > 1 ? 'The following subscriptions were created: ' : 'This subscription was created: ';
                $links = array();
                foreach ($subscriptions as $subscription) {
                    $subscriptionUrl = $helper->getSubscriptionUrl($subscription->getId());
                    $links[] = "<a href='{$subscriptionUrl}' class='subscription-url' data-id='{$subscription->getId()}'>#{$subscription->getId()}</a>";
                }

                Mage::getSingleton('core/session')->addSuccess($helper->__($message) . implode(', ', $links));
            }
        } catch (Exception $e) {
            Mage::log("Unable to create subscription from orderIncrementId={$order->getIncrementId()}");
            Mage::logException($e);
        }
    }


    /**
     * Listens to payment_method_is_active and when quote has subscription products it will disables payment methods
     * that don't have a subscription implementation.
     *
     * @param Varien_Event_Observer $observer
     */
    public function isPaymentMethodAvailable(Varien_Event_Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $methodInstance = $observer->getEvent()->getMethodInstance();
        $result = $observer->getEvent()->getResult();

        // Try to disable payments that are set as available
        if ($result->isAvailable && $quote && $quote->getPssHasSubscriptions()) {
            $result->isAvailable = Mage::helper('sheep_subscription/payment')->isSubscriptionPayment($methodInstance->getCode());
        }
    }


    /**
     * Exclude subscription quotes from clean expired quotes cron
     *
     * @see Mage_Sales_Model_Observer::cleanExpiredQuotes
     * @param Varien_Event_Observer $observer
     */
    public function whitelistSubscriptionQuotes(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Observer $salesObserver */
        $salesObserver = $observer->getEvent()->getSalesObserver();
        $fields = $salesObserver->getExpireQuotesAdditionalFilterFields();
        $fields['pss_is_subscription'] = array('neq' => Sheep_Subscription_Model_Subscription::QUOTE_IS_SUBSCRIPTION_YES);

        $salesObserver->setExpireQuotesAdditionalFilterFields($fields);
    }
}

