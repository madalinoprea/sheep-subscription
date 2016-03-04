<?php

/**
 * Sheep_Queue_Test_Config_Base adds tests for configuration xml files
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Config_Base extends EcomDev_PHPUnit_Test_Case_Config
{

    public function testHelperAliases()
    {
        $this->assertHelperAlias('sheep_subscription', 'Sheep_Subscription_Helper_Data');
        $this->assertHelperAlias('sheep_subscription/payment', 'Sheep_Subscription_Helper_Payment');
        $this->assertHelperAlias('sheep_subscription/product', 'Sheep_Subscription_Helper_Product');
        $this->assertHelperAlias('sheep_subscription/renewal', 'Sheep_Subscription_Helper_Renewal');
        $this->assertHelperAlias('sheep_subscription/subscription', 'Sheep_Subscription_Helper_Subscription');
        $this->assertHelperAlias('sheep_subscription/type', 'Sheep_Subscription_Helper_Type');
    }

    public function testSetup()
    {
        $this->assertSetupResourceDefined('Sheep_Subscription', 'sheep_subscription_setup');
    }
    
    public function testModelAliases()
    {
        $this->assertModelAlias('sheep_subscription/type', 'Sheep_Subscription_Model_Type');
        $this->assertResourceModelAlias('sheep_subscription/type', 'Sheep_Subscription_Model_Resource_Type');
        $this->assertResourceModelAlias('sheep_subscription/type_collection', 'Sheep_Subscription_Model_Resource_Type_Collection');
    }

    public function testObservers()
    {
        $this->assertEventObserverDefined('adminhtml', 'core_block_abstract_prepare_layout_after', 'sheep_subscription/adminhtml_observer', 'addSubscriptionTabs');
        $this->assertEventObserverDefined('adminhtml', 'catalog_product_prepare_save', 'sheep_subscription/adminhtml_observer', 'prepareProductSave');
        $this->assertEventObserverDefined('adminhtml', 'catalog_product_save_before', 'sheep_subscription/adminhtml_observer', 'saveProductSubscriptionTypes');

        $this->assertEventObserverDefined('frontend', 'payment_method_is_active', 'sheep_subscription/observer', 'isPaymentMethodAvailable');
        $this->assertEventObserverDefined('frontend', 'sales_model_service_quote_submit_after', 'sheep_subscription/observer', 'createSubscription');
    }
}
