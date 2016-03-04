<?php
/**
 * Class Sheep_Subscription_Test_Model_Notification_Cron
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_Model_Notification_Cron
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Model_Notification_Cron extends EcomDev_PHPUnit_Test_Case
{

    public function testGetNotificationService()
    {
        $model = Mage::getModel('sheep_subscription/notification_cron');
        $service = $model->getNotificationService();
        $this->assertNotNull($service);
        $this->assertInstanceOf('Sheep_Subscription_Model_Notification_Service', $service);
    }


    public function testCheckNextWeekUpcomingRenewals()
    {
        $helper = $this->getHelperMock('sheep_subscription/renewal', array('isUpcomingRenewalsNotificationEnabled', 'getUpcomingRenewalDateThreshold'));
        $helper->expects($this->any())->method('isUpcomingRenewalsNotificationEnabled')->willReturn(true);
        $helper->expects($this->any())->method('getUpcomingRenewalDateThreshold')->willReturn('2016-02-04 00:00:00');
        $this->replaceByMock('helper', 'sheep_subscription/renewal', $helper);

        $service = $this->getModelMock('sheep_subscription/notification_service', array('addNotificationEventsForUpcomingRenewals'));
        $service->expects($this->once())->method('addNotificationEventsForUpcomingRenewals')
            ->with($this->anything(), '2016-02-04 00:00:00')
            ->willReturn(3);

        $model = $this->getModelMock('sheep_subscription/notification_cron', array('getNotificationService', 'log'));
        $model->expects($this->once())->method('getNotificationService')->willReturn($service);

        $actual = $model->checkNextWeekUpcomingRenewals();
        $this->assertEquals('3 notification events for upcoming renewals were added', $actual);
    }


    public function testProcessNotifications()
    {
        $service = $this->getModelMock('sheep_subscription/notification_service', array('processNotificationQueue'));
        $service->expects($this->once())->method('processNotificationQueue');

        $model = $this->getModelMock('sheep_subscription/notification_cron', array('getNotificationService'));
        $model->expects($this->once())->method('getNotificationService')->willReturn($service);

        $model->processNotifications();
    }


    public function testCheckExpiredPaymentNotification()
    {
        $helper = $this->getHelperMock('sheep_subscription/payment', array('isExpiredPaymentNotificationEnabled', 'getExpirationDateThreshold'));
        $helper->expects($this->any())->method('isExpiredPaymentNotificationEnabled')->willReturn(true);
        $helper->expects($this->once())->method('getExpirationDateThreshold')->willReturn('2016-02-12');
        $this->replaceByMock('helper', 'sheep_subscription/payment', $helper);

        $service = $this->getModelMock('sheep_subscription/notification_service', array('addNotificationEventsForExpiredPayments'));
        $service->expects($this->once())->method('addNotificationEventsForExpiredPayments')->with('2016-02-12')->willReturn(3);

        $model = $this->getModelMock('sheep_subscription/notification_cron', array('getNotificationService', 'log'));
        $model->expects($this->any())->method('getNotificationService')->willReturn($service);

        $actual = $model->checkExpiredPaymentNotification();
        $this->assertEquals('3 notification events for expired payments were added.', $actual);
    }


    public function testCheckExpiredPaymentNotificationDisabled()
    {
        $helper = $this->getHelperMock('sheep_subscription/payment', array('isExpiredPaymentNotificationEnabled'));
        $helper->expects($this->any())->method('isExpiredPaymentNotificationEnabled')->willReturn(false);
        $this->replaceByMock('helper', 'sheep_subscription/payment', $helper);

        $service = $this->getModelMock('sheep_subscription/notification_service', array('addNotificationEventsForExpiredPayments'));
        $service->expects($this->never())->method('addNotificationEventsForExpiredPayments');

        $model = $this->getModelMock('sheep_subscription/notification_cron', array('getNotificationService', 'log'));
        $model->expects($this->any())->method('getNotificationService')->willReturn($service);

        $actual = $model->checkExpiredPaymentNotification();
        $this->assertEquals('disabled by config', $actual);
    }
}
