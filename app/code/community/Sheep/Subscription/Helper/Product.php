<?php

/**
 * Class Sheep_Subscription_Helper_Product
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Helper_Product extends Mage_Core_Helper_Abstract
{
    const PRODUCT_PURCHASE_ONLY = 0;
    const PRODUCT_SUBSCRIPTION = 1;
    const PRODUCT_SUBSCRIPTION_ONLY = 2;

    // Constants for associated product subscription types
    const PRODUCT_SUBSCRIPTION_TYPES_NONE = 'N';
    const PRODUCT_SUBSCRIPTION_TYPES_ALL = 'A';
    const PRODUCT_SUBSCRIPTION_TYPES_CUSTOM = 'C';


    /**
     * Returns an array with all product type ids that can be used for subscriptions
     *
     * @return array
     */
    public function getEnabledProductTypeIds()
    {
        return array(
            Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
            Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL,
            Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE,
            Mage_Catalog_Model_Product_Type::TYPE_BUNDLE,
            Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE,
        );
    }


    /**
     * Checks if subscription type can be used for specified product type
     *
     * @param string $productTypeId
     * @return bool
     */
    public function isEnabledForProductType($productTypeId)
    {
        return array_key_exists($productTypeId, array_flip($this->getEnabledProductTypeIds()));
    }


    /**
     * Returns product subscription options
     *
     * @return array
     */
    public function getIsSubscriptionOptions()
    {
        return array(
            self::PRODUCT_PURCHASE_ONLY     => $this->__('One Time Purchase Only'),
            self::PRODUCT_SUBSCRIPTION      => $this->__('Subscription & One Time Purchase'),
            self::PRODUCT_SUBSCRIPTION_ONLY => $this->__('Subscription Only')
        );
    }


    /**
     * Returns product subscription type value for multiselect.
     *
     * @see Sheep_Subscription_Helper_Product::getSubscriptionTypeOptions to view available options
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getProductSubscriptionTypeIdsValues(Mage_Catalog_Model_Product $product)
    {
        $subscriptionTypeIds = array();
        if ($product->getPssSubscriptionType() == self::PRODUCT_SUBSCRIPTION_TYPES_ALL) {
            $subscriptionTypeIds[] = self::PRODUCT_SUBSCRIPTION_TYPES_ALL;
        } else if ($product->getPssSubscriptionType() == self::PRODUCT_SUBSCRIPTION_TYPES_CUSTOM) {
            $subscriptionTypeIds = $this->_getProductSubscriptionTypeIds($product);
        }

        return $subscriptionTypeIds;
    }


    /**
     * Returns subscription type options that can be enabled for a product
     *
     * @return array
     */
    public function getSubscriptionTypeOptions($store = null)
    {
        $options = array();
        $options[] = array(
            'label' => $this->__('All active subscription types'),
            'value' => self::PRODUCT_SUBSCRIPTION_TYPES_ALL
        );

        $activeSubscriptionTypes = Mage::helper('sheep_subscription/type')->getAvailableTypes($store);
        /** @var Sheep_Subscription_Model_Type $activeSubscriptionType */
        foreach ($activeSubscriptionTypes as $activeSubscriptionType) {
            $options[] = array(
                'label' => $activeSubscriptionType->getTitle(),
                'value' => $activeSubscriptionType->getId()
            );
        }

        return $options;
    }


    /**
     * Returns if product is sold as subscription
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function isSubscriptionProduct(Mage_Catalog_Model_Product $product)
    {
        return $product->getPssIsSubscription() == self::PRODUCT_SUBSCRIPTION || $product->getPssIsSubscription() == self::PRODUCT_SUBSCRIPTION_ONLY;
    }


    /**
     * Returns subscription types associated to a products. Is bypassing other production subscription
     * attributes.
     *
     * Usage allowed only in this class by @see Sheep_Subscription_Helper_Product::_getProductSubscriptionTypeIds
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Sheep_Subscription_Model_Resource_ProductType_Collection
     */
    protected function _getProductSubscriptionTypes(Mage_Catalog_Model_Product $product)
    {
        /** @var Sheep_Subscription_Model_Resource_ProductType_Collection $productSubscriptionType */
        $productSubscriptionType = Mage::getModel('sheep_subscription/productType')->getCollection();
        $productSubscriptionType->addProductToFilter($product->getId());

        return $productSubscriptionType;
    }


    /**
     * Returns ids for subscription types associated to a products. Is bypassing other production subscription
     * attributes.
     *
     * Usage allowed only in this class by @see Sheep_Subscription_Helper_Product::setProductSubscriptionTypes
     *
     * To retrieve subscription types associated to a product use @see Sheep_Subscription_Helper_Product::getProductSubscriptionTypes
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    protected function _getProductSubscriptionTypeIds(Mage_Catalog_Model_Product $product)
    {
        $productSubscriptionTypes = $this->_getProductSubscriptionTypes($product);
        $productSubscriptionTypes->addFieldToSelect('type_id');
        $productSubscriptionTypesData = $productSubscriptionTypes->getData();

        $productSubscriptionTypeIds = array();
        foreach ($productSubscriptionTypesData as $productSubscriptionTypeData) {
            $productSubscriptionTypeIds[] = $productSubscriptionTypeData['type_id'];
        }

        return $productSubscriptionTypeIds;
    }


    /**
     * Returns subscription types associated to specified product
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Varien_Data_Collection
     */
    public function getProductSubscriptionTypes(Mage_Catalog_Model_Product $product)
    {
        $subscriptionTypes = null;
        if (!$this->isSubscriptionProduct($product) || $product->getPssSubscriptionType() == self::PRODUCT_SUBSCRIPTION_TYPES_NONE) {
            return new Varien_Data_Collection();
        }

        switch ($product->getPssSubscriptionType()) {
            case self::PRODUCT_SUBSCRIPTION_TYPES_ALL:
                $subscriptionTypes = Mage::helper('sheep_subscription/type')->getAvailableTypes($product->getStoreId());
                break;
            case self::PRODUCT_SUBSCRIPTION_TYPES_CUSTOM:
                /** @var Sheep_Subscription_Model_Resource_ProductType_Collection $productSubscriptionType */
                $typeIds = $this->_getProductSubscriptionTypeIds($product);
                $subscriptionTypes = Mage::helper('sheep_subscription/type')->getAvailableTypes($product->getStoreId());
                $subscriptionTypes->addFieldToFilter('id', array('in' => $typeIds));
                break;
            default:
                $subscriptionTypes = new Varien_Data_Collection();
        }

        return $subscriptionTypes;
    }


    /**
     * We don't add any association when subscriptionTypeIds contains 0 = All active subscription types.
     * All current active subscription type will be returned by @see Sheep_Subscription_Helper_Product::getProductSubscriptionTypes
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $subscriptionTypeIds
     * @return $this
     */
    public function setProductSubscriptionTypes(Mage_Catalog_Model_Product $product, array $subscriptionTypeIds)
    {
        if (!$product->getId()) {
            return $this;
        }

        $previousProductSubscriptionTypeIds = $this->_getProductSubscriptionTypeIds($product);

        $pssSubscriptionType = self::PRODUCT_SUBSCRIPTION_TYPES_CUSTOM;
        $addSubscriptionTypeIds = array_diff($subscriptionTypeIds, $previousProductSubscriptionTypeIds);
        $deleteSubscriptionTypeIds = array_diff($previousProductSubscriptionTypeIds, $subscriptionTypeIds);

        if (in_array(self::PRODUCT_SUBSCRIPTION_TYPES_ALL, $subscriptionTypeIds)) {
            // Delete all previous subscription ids, don't add anything
            $pssSubscriptionType = self::PRODUCT_SUBSCRIPTION_TYPES_ALL;
            $deleteSubscriptionTypeIds = $previousProductSubscriptionTypeIds;
            $addSubscriptionTypeIds = array();
        }

        if (!$subscriptionTypeIds) {
            $pssSubscriptionType = self::PRODUCT_SUBSCRIPTION_TYPES_NONE;
            $deleteSubscriptionTypeIds = $previousProductSubscriptionTypeIds;
            $addSubscriptionTypeIds = array();
        }

        // Saves what type of subscription types are used for this product, remove and add subscription types
        $product->setPssSubscriptionType($pssSubscriptionType);

        if ($deleteSubscriptionTypeIds) {
            $subscriptionTypes = Mage::getModel('sheep_subscription/productType')->getCollection();
            $subscriptionTypes->addProductToFilter($product->getId());
            $subscriptionTypes->addSubscriptionTypeFilter($deleteSubscriptionTypeIds);
            $subscriptionTypes->walk('delete');
        }

        foreach ($addSubscriptionTypeIds as $typeId) {
            /** @var Sheep_Subscription_Model_Product_Type $productSubscriptionType */
            $productSubscriptionType = Mage::getModel('sheep_subscription/productType');
            $productSubscriptionType->setProductId($product->getId());
            $productSubscriptionType->setTypeId($typeId);
            $productSubscriptionType->save();
        }
    }


    /**
     * Return type price overrides for this product
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Sheep_Subscription_Model_Resource_ProductTypePrice_Collection
     */
    public function getProductSubscriptionTypePrices(Mage_Catalog_Model_Product $product)
    {
        /** @var Sheep_Subscription_Model_Resource_ProductTypePrice_Collection $typePrices */
        $typePrices = Mage::getModel('sheep_subscription/productTypePrice')->getCollection();
        $typePrices->addProductToFilter($product->getId());

        return $typePrices;
    }


    /**
     * Saves subscription type prices for specified product.
     *
     * Previous prices are deleted.
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $newTypePrices
     */
    public function setProductSubscriptionTypePrices(Mage_Catalog_Model_Product $product, array $newTypePrices)
    {
        if (!$product->getId()) {
            return;
        }

        // Delete already saved
        /** @var Sheep_Subscription_Model_Resource_ProductTypePrice_Collection $typePrices */
        $typePrices = Mage::getModel('sheep_subscription/productTypePrice')->getCollection();
        $typePrices->addProductToFilter($product->getId());
        $typePrices->walk('delete');

        // Save new type prices
        if ($newTypePrices) {
            $productTypes = $this->getProductSubscriptionTypes($product);
            $typePrices->clear();

            foreach ($newTypePrices as $typePrice) {
                // Don't save price specified for type that is no longer associated to this product
                if (!$productTypes->getItemById($typePrice['type_id'])) {
                    continue;
                }

                $typePrice['discount'] = (float)$typePrice['discount'];
                $typePrice['discount_percent'] = (float)$typePrice['discount_percent'];

                // Ignore type prices where both discount and discount_percent are zero
                if (!($typePrice['discount'] || $typePrice['discount_percent'])) {
                    continue;
                }

                /** @var Sheep_Subscription_Model_ProductTypePrice $typePriceModel */
                $typePriceModel = Mage::getModel('sheep_subscription/productTypePrice');
                $typePriceModel->setProductId($product->getId());
                $typePriceModel->setTypeId($typePrice['type_id']);
                $typePriceModel->setDiscount($typePrice['discount']);
                $typePriceModel->setDiscountPercent($typePrice['discount_percent']);
                $typePrices->addItem($typePriceModel);
            }
            $typePrices->save();
        }
    }

}
