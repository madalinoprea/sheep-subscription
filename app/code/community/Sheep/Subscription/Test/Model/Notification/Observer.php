<?php
/**
 * Class Sheep_Subscription_Test_Model_Notification_Observer
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_Model_Notification_Observer
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Model_Notification_Observer extends EcomDev_PHPUnit_Test_Case
{

    /**
     * @covers Sheep_Subscription_Model_Notification_Observer::onRenewalCompleted
     */
    public function testOnRenewalCompleted()
    {

        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getCustomerId'));
        $subscription->expects($this->any())->method('getCustomerId')->willReturn(101);

        $renewal = $this->getModelMock('sheep_subscription/renewal', array('getSubscription'));
        $renewal->expects($this->once())->method('getSubscription')->willReturn($subscription);

        $observerEvent = new Varien_Event_Observer(array(
            'event' => new Varien_Event(array(
                'renewal' => $renewal
            ))
        ));

        $service = $this->getModelMock('sheep_subscription/notification_service', array('notifyCustomer'));
        $service->expects($this->once())->method('notifyCustomer')->with(101);
        $this->replaceByMock('model', 'sheep_subscription/notification_service', $service);

        $observer = Mage::getModel('sheep_subscription/notification_observer');
        $observer->onRenewalCompleted($observerEvent);
    }

}
