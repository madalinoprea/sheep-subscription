<?php

/**
 * Class Sheep_Subscription_Block_Subscription_Edit_ShippingAddress
 *
 * @category Sheep
 * @package Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Block_Subscription_Edit_ShippingAddress extends Mage_Checkout_Block_Onepage_Shipping_Method_Available
{
    protected $subscription;


    /**
     * Returns current subscription
     *
     * @return Sheep_Subscription_Model_Subscription
     */
    public function getSubscription()
    {
        if ($this->subscription === null) {
            $this->subscription = Mage::registry('pss_subscription');
        }

        return $this->subscription;
    }


    /**
     * Returns subscription url
     *
     * @return string
     */
    public function getBackUrl()
    {
        return Mage::helper('sheep_subscription')->getSubscriptionUrl($this->getSubscription()->getId());
    }

    /**
     * Returns url where data needs to be posted to
     *
     * @return string
     */
    public function getSaveShippingAddressUrl()
    {
        return Mage::helper('sheep_subscription')->getSaveShippingAddressUrl($this->getSubscription()->getId());
    }


    /**
     * Instructs what quote is used
     *
     * @return Mage_Sales_Model_Quote|null
     */
    public function getQuote()
    {
        return $this->getSubscription()->getQuote();
    }


    /**
     * Shipping address that is currently set
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    public function getAddress()
    {
        return $this->getQuote()->getShippingAddress();
    }


    /**
     * Prevents any calls that might affect customer's checkout session
     *
     * @return null
     */
    public function getCheckout()
    {
        return null;
    }


    /**
     * Similar to parent method except that we register Js change handles from JS
     *
     * @param $type
     * @return string
     */
    public function getAddressesHtmlSelect($type)
    {
        if ($this->isCustomerLoggedIn()) {
            $options = array();
            foreach ($this->getCustomer()->getAddresses() as $address) {
                $options[] = array(
                    'value' => $address->getId(),
                    'label' => $address->format('oneline')
                );
            }

            $addressId = $this->getAddress()->getCustomerAddressId();
            if (empty($addressId)) {
                if ($type=='billing') {
                    $address = $this->getCustomer()->getPrimaryBillingAddress();
                } else {
                    $address = $this->getCustomer()->getPrimaryShippingAddress();
                }
                if ($address) {
                    $addressId = $address->getId();
                }
            }

            $select = $this->getLayout()->createBlock('core/html_select')
                ->setName($type.'_address_id')
                ->setId($type.'-address-select')
                ->setClass('address-select')
//                ->setExtraParams('onchange="'.$type.'.newAddress(!this.value)"')
                ->setValue($addressId)
                ->setOptions($options);

            $select->addOption('', Mage::helper('checkout')->__('New Address'));

            return $select->getHtml();
        }
        return '';
    }

}
