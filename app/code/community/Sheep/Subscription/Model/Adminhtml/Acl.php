<?php

/**
 * Class Sheep_Subscription_Model_Adminhtml_Acl
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2016, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Model_Adminhtml_Acl
{
    const CATALOG_SUBSCRIPTION_TYPES_VIEW_ACL = 'admin/catalog/sheep_subscription/view_types';
    const CATALOG_SUBSCRIPTION_TYPES_EDIT_ACL = 'admin/catalog/sheep_subscription/edit_types';

    const PRODUCT_SUBSCRIPTION_CONFIGURATION_VIEW_ACL = 'admin/catalog/sheep_subscription/view_product_setup';
    const PRODUCT_SUBSCRIPTION_CONFIGURATION_EDIT_ACL = 'admin/catalog/sheep_subscription/edit_product_setup';
    const PRODUCT_SUBSCRIPTION_PRICES_VIEW_ACL = 'admin/catalog/sheep_subscription/view_product_subscription_prices';
    const PRODUCT_SUBSCRIPTION_PRICES_EDIT_ACL = 'admin/catalog/sheep_subscription/edit_product_subscription_prices';

    const CUSTOMER_SUBSCRIPTION_VIEW_ACL = 'admin/customer/sheep_subscription_view';

    const SALES_SUBSCRIPTION_VIEW_ACL =  'admin/sales/sheep_subscription/view_subscriptions';
    const SALES_SUBSCRIPTION_VIEW_DETAILS_ACL =  'admin/sales/sheep_subscription/view_subscription_details';
    const SALES_SUBSCRIPTION_VIEW_RENEWALS_ACL =  'admin/sales/sheep_subscription/view_subscription_renewals';
    const SALES_SUBSCRIPTION_EDIT_ACL =  'admin/sales/sheep_subscription/edit_subscription';

    const SALES_RENEWAL_VIEW_ACL = 'admin/sales/sheep_subscription/view_renewals';

    const REPORT_SUBSCRIPTION_NEW_SUBSCRIBERS_ACL = 'admin/report/sheep_subscription/new_subscribers';
    const REPORT_SUBSCRIPTION_NEW_SUBSCRIPTIONS_ACL = 'admin/report/sheep_subscription/new_subscriptions';
    const REPORT_SUBSCRIPTION_CANCELED_SUBSCRIPTIONS_ACL = 'admin/report/sheep_subscription/canceled_subscriptions';
    const REPORT_SUBSCRIPTION_BEST_RECURRING_PRODUCTS_ACL = 'admin/report/sheep_subscription/best_recurring_products';
    const REPORT_SUBSCRIPTION_SCHEDULED_RENEWALS_ACL = 'admin/report/sheep_subscription/scheduled_renewals';
    const REPORT_SUBSCRIPTION_RENEWAL_TIMELINE_ACL = 'admin/report/sheep_subscription/renewal_timeline';
    const REPORT_SUBSCRIPTION_INVENTORY_PLANNING_ACL = 'admin/report/sheep_subscription/inventory_planning';

    /**
     * @return Mage_Admin_Model_Session
     */
    public function getAdminSession()
    {
        return Mage::getSingleton('admin/session');
    }


    /**
     * Admin user can view subscription types
     *
     * @return bool
     */
    public function canViewSubscriptionTypes()
    {
        return
            $this->getAdminSession()->isAllowed(self::CATALOG_SUBSCRIPTION_TYPES_VIEW_ACL) ||
            $this->canEditSubscriptionTypes();
    }


    /**
     * Admin user can edit subscription types
     *
     * @return bool
     */
    public function canEditSubscriptionTypes()
    {
        return $this->getAdminSession()->isAllowed(self::CATALOG_SUBSCRIPTION_TYPES_EDIT_ACL);
    }


    /**
     * Admin user can view product subscription configuration but he cannot change it
     *
     * @return bool
     */
    public function canViewProductSubscriptionConfiguration()
    {
        return $this->getAdminSession()->isAllowed(self::PRODUCT_SUBSCRIPTION_CONFIGURATION_VIEW_ACL);
    }


    /**
     * Admin user can view and change product subscription configuration
     *
     * @return bool
     */
    public function canEditProductSubscriptionConfiguration()
    {
        return $this->getAdminSession()->isAllowed(self::PRODUCT_SUBSCRIPTION_CONFIGURATION_EDIT_ACL);
    }


    /**
     * Admin user can view product subscription prices but he cannot change them
     *
     * @return bool
     */
    public function canViewProductSubscriptionPrices()
    {
        return $this->getAdminSession()->isAllowed(self::PRODUCT_SUBSCRIPTION_PRICES_VIEW_ACL);
    }


    /**
     * Admin user can view and change product subscription prices
     *
     * @return bool
     */
    public function canEditProductSubscriptionPrices()
    {
        return $this->getAdminSession()->isAllowed(self::PRODUCT_SUBSCRIPTION_PRICES_EDIT_ACL);
    }


    /**
     * We show subscription tab on product page if current user is allowed to view/edit configuration, view/edit subscription prices
     *
     * @return bool
     */
    public function canShowProductSubscriptionTab()
    {
        return
            $this->canViewProductSubscriptionConfiguration() ||
            $this->canEditProductSubscriptionConfiguration() ||
            $this->canViewProductSubscriptionPrices() ||
            $this->canEditProductSubscriptionPrices();
    }


    /**
     * Admin user can view customer subscriptions
     *
     * @return bool
     */
    public function canViewCustomerSubscription()
    {
        return $this->getAdminSession()->isAllowed(self::CUSTOMER_SUBSCRIPTION_VIEW_ACL);
    }


    /**
     * We show subscription tab on customer page if current user is allowed to view customer subscriptions or subscription list
     *
     * @return bool
     */
    public function canShowCustomerSubscriptionTab()
    {
        return $this->canViewCustomerSubscription();
    }


    /**
     * Admin user can view subscription list
     *
     * @return bool
     */
    public function canViewSubscriptions()
    {
        return $this->getAdminSession()->isAllowed(self::SALES_SUBSCRIPTION_VIEW_ACL);
    }


    /**
     * Admin user can view subscription page
     *
     * @return bool
     */
    public function canViewSubscriptionDetails()
    {
        return $this->getAdminSession()->isAllowed(self::SALES_SUBSCRIPTION_VIEW_DETAILS_ACL);
    }


    /**
     * Admin user can edit subscription (pause, resume, cancel, etc.)
     *
     * @return bool
     */
    public function canEditSubscription()
    {
        return $this->getAdminSession()->isAllowed(self::SALES_SUBSCRIPTION_EDIT_ACL);
    }


    /**
     * Admin user can view renewal tab on subscription page
     *
     * @return bool
     */
    public function canViewSubscriptionRenewals()
    {
        return $this->getAdminSession()->isAllowed(self::SALES_SUBSCRIPTION_VIEW_RENEWALS_ACL);
    }


    /**
     * Admin user can view renewals grid
     *
     * @return bool
     */
    public function canViewRenewals()
    {
        return $this->getAdminSession()->isAllowed(self::SALES_RENEWAL_VIEW_ACL);
    }


    /**
     * Admin user can view/export new subscribers report
     *
     * @return bool
     */
    public function canViewNewSubscribersReport()
    {
        return $this->getAdminSession()->isAllowed(self::REPORT_SUBSCRIPTION_NEW_SUBSCRIBERS_ACL);
    }


    /**
     * Admin user can view / export new subscriptions report
     *
     * @return bool
     */
    public function canViewNewSubscriptionsReport()
    {
        return $this->getAdminSession()->isAllowed(self::REPORT_SUBSCRIPTION_NEW_SUBSCRIPTIONS_ACL);
    }


    /**
     * Admin user can view / export canceled subscriptions report
     *
     * @return bool
     */
    public function canViewCanceledSubscriptionsReport()
    {
        return $this->getAdminSession()->isAllowed(self::REPORT_SUBSCRIPTION_CANCELED_SUBSCRIPTIONS_ACL);
    }


    /**
     * Admin user can view/export best recurring products report
     *
     * @return bool
     */
    public function canViewBestRecurringProductsReport()
    {
        return $this->getAdminSession()->isAllowed(self::REPORT_SUBSCRIPTION_BEST_RECURRING_PRODUCTS_ACL);
    }

    /**
     * Admin user can view / export scheduled renewals report
     *
     * @return bool
     */
    public function canViewScheduledRenewalsReport()
    {
        return $this->getAdminSession()->isAllowed(self::REPORT_SUBSCRIPTION_SCHEDULED_RENEWALS_ACL);
    }


    /**
     * Admin user can view / export renewal timeline report
     *
     * @return bool
     */
    public function canViewRenewalTimelineReport()
    {
        return $this->getAdminSession()->isAllowed(self::REPORT_SUBSCRIPTION_RENEWAL_TIMELINE_ACL);
    }


    /**
     * Admin user can view / export inventory planning report
     *
     * @return bool
     */
    public function canViewInventoryPlanningReport()
    {
        return $this->getAdminSession()->isAllowed(self::REPORT_SUBSCRIPTION_INVENTORY_PLANNING_ACL);
    }

}
