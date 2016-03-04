<?php

/**
 * Class Sheep_Subscription_Controller_Adminhtml_Action
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2016, All Rights reserved.
 * @link     https://moprea.ro
 */
class Sheep_Subscription_Controller_Adminhtml_Action extends Mage_Adminhtml_Controller_Action
{

    /**
     * Checks ACL for specified resource and sets a redirect to denied page if resource is not accessible for current user
     *
     * Useful if we have Adminhtml controllers that offer actions with different ACL
     *
     * @param string $resource
     * @return bool
     */
    public function isAllowedOrRedirect($resource)
    {
        /** @var Mage_Admin_Model_Session $session */
        $session = Mage::getSingleton('admin/session');
        if (!$session->isAllowed($resource)) {
            $this->forwardDenied();

            return false;
        }

        return true;
    }


    /**
     * Shows admin denied page
     */
    public function forwardDenied()
    {
        $this->_forward('denied');
        $this->setFlag('', self::FLAG_NO_DISPATCH, true);
    }


    /**
     * Returns module's Admin ACL model
     *
     * @return Sheep_Subscription_Model_Adminhtml_Acl
     */
    public function getAcl()
    {
        return Mage::getSingleton('sheep_subscription/adminhtml_acl');
    }
}
