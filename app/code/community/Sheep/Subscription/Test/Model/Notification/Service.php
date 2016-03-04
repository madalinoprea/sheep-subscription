<?php

/**
 * Class Sheep_Subscription_Test_Model_Notification_Service
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers   Sheep_Subscription_Model_Notification_Service
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Model_Notification_Service extends EcomDev_PHPUnit_Test_Case
{

    /**
     * @covers Sheep_Subscription_Model_Notification_Service::getNotificationQueue
     */
    public function testGetNotificationQueue()
    {
        $helper = $this->getHelperMock('sheep_queue', array('getQueue'));
        $helper->expects($this->once())->method('getQueue')->with(Sheep_Subscription_Model_Notification_Service::NOTIFICATION_QUEUE);
        $this->replaceByMock('helper', 'sheep_queue', $helper);

        $service = Mage::getModel('sheep_subscription/notification_service');
        $service->getNotificationQueue();
    }


    /**
     * @covers Sheep_Subscription_Model_Notification_Service::getUpcomingRenewals
     */
    public function testGetUpcomingRenewals()
    {
        $renewals = $this->getResourceModelMock('sheep_subscription/renewal_collection', array('addStatusFilter', 'addBetweenFilter'));
        $renewals->expects($this->once())->method('addStatusFilter')->with(Sheep_Subscription_Model_Renewal::STATUS_PENDING)->willReturnSelf();
        $renewals->expects($this->once())->method('addBetweenFilter')->with('2015-11-01', '2015-12-31')->willReturnSelf();
        $this->replaceByMock('resource_model', 'sheep_subscription/renewal_collection', $renewals);


        $service = Mage::getModel('sheep_subscription/notification_service');
        $service->getUpcomingRenewals('2015-11-01', '2015-12-31');
    }


    /**
     * @covers Sheep_Subscription_Model_Notification_Service::getCustomerProfile
     */
    public function testGetCustomerProfile()
    {
        $profileModel = $this->getModelMock('sheep_subscription/profile', array('load'));
        $profileModel->expects($this->once())->method('load')->with(100);
        $this->replaceByMock('model', 'sheep_subscription/profile', $profileModel);

        $service = Mage::getModel('sheep_subscription/notification_service');
        $service->getCustomerProfile(100);
    }


    /**
     * @covers Sheep_Subscription_Model_Notification_Service::addNotificationEventsForUpcomingRenewals
     */
    public function testAddNotificationEventsForUpcomingRenewals()
    {
        $renewals = $this->getResourceModelMock('sheep_subscription/renewal_collection', array('addSubscriptionData', 'load', 'getIterator'));
        $renewals->expects($this->any())->method('getUpcomingRenewals')->with(array('customer_id'));
        $renewals->expects($this->once())->method('getIterator')->willReturn(
            new ArrayIterator(array(
                    new Varien_Object(array('customer_id' => 101)),
                    new Varien_Object(array('customer_id' => 102))
                )
            ));

        $service = $this->getModelMock('sheep_subscription/notification_service', array('getUpcomingRenewals', 'notifyCustomer'));
        $service->expects($this->once())->method('getUpcomingRenewals')->with('2015-11-01', '2015-12-01')->willReturn($renewals);
        $service->expects($this->at(1))->method('notifyCustomer')->with(101);
        $service->expects($this->at(2))->method('notifyCustomer')->with(102);

        $service->addNotificationEventsForUpcomingRenewals('2015-11-01', '2015-12-01');
    }


    /**
     * @covers Sheep_Subscription_Model_Notification_Service::getExpiredPayments
     */
    public function testGetExpiredPayments()
    {
        $payments = $this->getResourceModelMock('sheep_subscription/payment_collection', array('addEarlierFilter', 'addSubscriptionData', 'addFieldToFilter'));
        $payments->expects($this->once())->method('addEarlierFilter')->with('2016-02-13');
        $payments->expects($this->once())->method('addSubscriptionData')->with(array('status', 'customer_id'));
        $payments->expects($this->once())->method('addFieldToFilter')->with('status', Sheep_Subscription_Model_Subscription::STATUS_ACTIVE);
        $this->replaceByMock('resource_model', 'sheep_subscription/payment_collection', $payments);

        $service = Mage::getModel('sheep_subscription/notification_service');
        $service->getExpiredPayments('2016-02-13');
    }


    /**
     * @covers Sheep_Subscription_Model_Notification_Service::addNotificationEventsForExpiredPayments
     */
    public function testAddNotificationEventsForExpiredPayments()
    {
        $select = $this->getMock('Varien_Db_Select', array('group'), array(), '', false);
        $select->expects($this->once())->method('group')->with('customer_id');

        $payments = $this->getResourceModelMock('sheep_subscription/payment_collection', array('_initSelect', 'getSelect', 'getData', 'load'));
        $payments->expects($this->once())->method('getSelect')->willReturn($select);
        $payments->expects($this->once())->method('getData')->willReturn(
            array(
                array('customer_id' => 101, 'subscription_id' => 5),
                array('customer_id' => 102, 'subscription_id' => 6)
            )
        );

        $service = $this->getModelMock('sheep_subscription/notification_service', array('getExpiredPayments', 'notifyCustomer'));
        $service->expects($this->once())->method('getExpiredPayments')->with('2016-02-29')->willReturn($payments);
        $service->expects($this->at(1))->method('notifyCustomer')->with(101);
        $service->expects($this->at(2))->method('notifyCustomer')->with(102);

        $service->addNotificationEventsForExpiredPayments('2016-02-29');
    }


    /**
     * @covers Sheep_Subscription_Model_Notification_Service::notifyCustomer
     */
    public function testNotifyCustomer()
    {
        $queue = $this->getMock('Zend_Queue', array('send'), array(), '', false);
        $queue->expects($this->once())->method('send')->with($this->stringContains('{"customer_id":102,"added_at"'));

        $service = $this->getModelMock('sheep_subscription/notification_service', array('getNotificationQueue'));
        $service->expects($this->any())->method('getNotificationQueue')->willReturn($queue);
        $service->notifyCustomer(102);
    }


    /**
     * @covers Sheep_Subscription_Model_Notification_Service::notifyCustomer
     */
    public function testNotifyCustomerWithoutCustomer()
    {
        $queue = $this->getMock('Zend_Queue', array('send'), array(), '', false);
        $queue->expects($this->never())->method('send');

        $service = $this->getModelMock('sheep_subscription/notification_service', array('getNotificationQueue'));
        $service->expects($this->any())->method('getNotificationQueue')->willReturn($queue);
        $service->notifyCustomer('');
    }


    /**
     * @covers Sheep_Subscription_Model_Notification_Service::processNotificationQueue
     */
    public function testProcessNotificationQueue()
    {
        $ZendQueueMessage = $this->getMockClass('Zend_Queue_Message', array('setQueue'), array(), '', true);
        $firstMessage= new $ZendQueueMessage(array('data' => array('message_id' => 101, 'body' => json_encode(array('customer_id' => 100)))));
        $secondMessage = new $ZendQueueMessage(array('data' => array('message_id' => 102, 'body' => json_encode(array('customer_id' => 103)))));

        $queue = $this->getMock('Zend_Queue', array('receive', 'deleteMessage'), array(), '', false);
        $queue->expects($this->once())->method('receive')->with(4, 3600)->willReturn(array($firstMessage, $secondMessage));
        $queue->expects($this->at(1))->method('deleteMessage')->with($firstMessage);
        $queue->expects($this->at(2))->method('deleteMessage')->with($secondMessage);

        /** @var Sheep_Subscription_Model_Notification_Service $service */
        $service = $this->getModelMock('sheep_subscription/notification_service', array('getNotificationQueue', 'processNotificationEvent'));
        $service->expects($this->any())->method('getNotificationQueue')->willReturn($queue);
        $service->expects($this->atLeast(2))->method('processNotificationEvent');
        $service->expects($this->at(1))->method('processNotificationEvent')->with(array('customer_id' => 100));
        $service->expects($this->at(2))->method('processNotificationEvent')->with(array('customer_id' => 103));

        $service->processNotificationQueue(4, 3600);
    }


    /**
     * @covers Sheep_Subscription_Model_Notification_Service::processNotificationQueue
     */
    public function testProcessNotificationQueueWithInvalidMessage()
    {
        $ZendQueueMessage = $this->getMockClass('Zend_Queue_Message', array('setQueue'), array(), '', true);
        $message = new $ZendQueueMessage(array('data' => array('message_id' => '111102', 'body' => 'not json')));

        $queue = $this->getMock('Zend_Queue', array('receive', 'deleteMessage'), array(), '', false);
        $queue->expects($this->once())->method('receive')->with(4, 3600)->willReturn(array($message));
        $queue->expects($this->never())->method('deleteMessage')->with($message);

        $service = $this->getModelMock('sheep_subscription/notification_service', array('getNotificationQueue', 'processNotificationEvent'));
        $service->expects($this->any())->method('getNotificationQueue')->willReturn($queue);
        $service->expects($this->never())->method('processNotificationEvent');

        $service->processNotificationQueue(4, 3600);

    }


    /**
     * @covers Sheep_Subscription_Model_Notification_Service::processNotificationQueue
     */
    public function testProcessNotificationEvent()
    {
        $profile = $this->getModelMock('sheep_subscription/profile', array('setCustomerId', 'setNotifiedAt', 'save'));
        $profile->expects($this->once())->method('setCustomerId')->with(100);
        $profile->expects($this->once())->method('setNotifiedAt');
        $profile->expects($this->once())->method('save');

        $service = $this->getModelMock('sheep_subscription/notification_service', array('getCustomerProfile', 'sendCustomerOverviewEmail'));
        $service->expects($this->any())->method('getCustomerProfile')->with(100)->willReturn($profile);
        $service->expects($this->once())->method('sendCustomerOverviewEmail')->with(100)->willReturn(true);

        $service ->processNotificationEvent(array('customer_id' => 100));
    }


    /**
     * @covers Sheep_Subscription_Model_Notification_Service::sendCustomerOverviewEmail
     */
    public function testSendCustomerOverviewEmail()
    {
        $customerMock = $this->getModelMock('customer/customer', array('load', 'getEmail', 'getName', 'getStore'));
        $customerMock->expects($this->once())->method('load')->with(100)->willReturnSelf();
        $customerMock->expects($this->any())->method('getEmail')->willReturn('mario@moprea.ro');
        $customerMock->expects($this->any())->method('getName')->willReturn('Mario Oprea');
        $customerMock->expects($this->any())->method('getStore')->willReturn(new Varien_Object(array('id' => 1)));
        $this->replaceByMock('model', 'customer/customer', $customerMock);

        $emailTemplateMock = $this->getModelMock('core/email_template', array('sendTransactional', 'getSentSuccess'));
        $emailTemplateMock->expects($this->once())->method('sendTransactional')->with(
            Sheep_Subscription_Model_Notification_Service::SUBSCRIPTION_OVERVIEW_EMAIL_TEMPLATE_ID,
            'sales',
            'mario@moprea.ro',
            'Mario Oprea',
            $this->logicalAnd($this->arrayHasKey('customer'), $this->arrayHasKey('subscription_list_url')),
            1
        );
        $emailTemplateMock->expects($this->once())->method('getSentSuccess')->willReturn(true);
        $this->replaceByMock('model', 'core/email_template', $emailTemplateMock);

        $model = Mage::getModel('sheep_subscription/notification_service');
        $actual = $model->sendCustomerOverviewEmail(100);
        $this->assertTrue($actual);
    }

    /**
     * @covers Sheep_Subscription_Model_Notification_Service::sendNewSubscriptionEmail
     */
    public function testSendNewSubscriptionEmail()
    {
        $subscription = $this->getModelMock('sheep_subscription/subscription');

        /** @var Sheep_Subscription_Model_Notification_Service $model */
        $model = $this->getModelMock('sheep_subscription/notification_service', array('sendSubscriptionEmail'));
        $model->expects($this->once())->method('sendSubscriptionEmail')->with(Sheep_Subscription_Model_Notification_Service::NEW_SUBSCRIPTION_EMAIL_TEMPLATE_ID, $subscription);
        $model->sendNewSubscriptionEmail($subscription);
    }

    /**
     * @covers Sheep_Subscription_Model_Notification_Service::_getEmailTemplateVariables
     */
    public function testGetEmailTemplateVariables()
    {
        $service = Mage::getModel('sheep_subscription/notification_service');

        $shipping = $this->getModelMock('sales/quote_address', array('getShippingDescription'));
        $shipping->expects($this->once())->method('getShippingDescription')->willReturn('shipping description');

        $payment = $this->getModelMock('sales/quote_payment');

        $quote = $this->getModelMock('sales/quote', array('getStoreId', 'getIsVirtual', 'getShippingAddress', 'getPayment'));
        $quote->expects($this->any())->method('getStoreId')->willReturn(10);
        $quote->expects($this->any())->method('getIsVirtual')->willReturn(false);
        $quote->expects($this->once())->method('getShippingAddress')->willReturn($shipping);
        $quote->expects($this->once())->method('getPayment')->willReturn($payment);

        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getId', 'getQuote'));
        $subscription->expects($this->any())->method('getId')->willReturn(100);
        $subscription->expects($this->any())->method('getQuote')->willReturn($quote);

        $paymentBlock = $this->getBlockMock('core/template', array('getMethod', '_toHtml'));
        $paymentBlock->expects($this->once())->method('getMethod')->willReturn(new Varien_Object());
        $paymentBlock->expects($this->any())->method('_toHtml')->willReturn('payment block html');

        $paymentHelper = $this->getHelperMock('payment', array('getInfoBlock'));
        $paymentHelper->expects($this->once())->method('getInfoBlock')->with($payment)->willReturn($paymentBlock);
        $this->replaceByMock('helper', 'payment', $paymentHelper);

        $helper = $this->getHelperMock('sheep_subscription', array('getSubscriptionUrlInStore'));
        $helper->expects($this->once())->method('getSubscriptionUrlInStore')->with(100, 10)->willReturn('subscription url');
        $this->replaceByMock('helper', 'sheep_subscription', $helper);

        $vars = EcomDev_Utils_Reflection::invokeRestrictedMethod($service, '_getEmailTemplateVariables', array($subscription));

        $this->assertNotEmpty($vars);
        $this->assertArrayHasKey('subscription', $vars);
        $this->assertArrayHasKey('subscription_url', $vars);
        $this->assertArrayHasKey('payment_html', $vars);
        $this->assertArrayHasKey('has_shipping', $vars);
        $this->assertArrayHasKey('shipping_description', $vars);

        $this->assertEquals($subscription, $vars['subscription']);
        $this->assertEquals('subscription url', $vars['subscription_url']);
        $this->assertEquals('payment block html', $vars['payment_html']);
        $this->assertEquals('shipping description', $vars['shipping_description']);
    }


    public function testSendSubscriptionEmail()
    {
        $customer = $this->getModelMock('customer/customer', array('getEmail', 'getName'));
        $customer->expects($this->once())->method('getEmail')->willReturn('customer@example.com');
        $customer->expects($this->once())->method('getName')->willReturn('Customer Name');

        $quote = $this->getModelMock('sales/quote', array('getStoreId'));
        $quote->expects($this->once())->method('getStoreId')->willReturn(10);

        $subscription = $this->getModelMock('sheep_subscription/subscription', array('getCustomer', 'getQuote'));
        $subscription->expects($this->any())->method('getCustomer')->willReturn($customer);
        $subscription->expects($this->any())->method('getQuote')->willReturn($quote);

        $templateVars = array(
            'subscription'     => $subscription,
            'subscription url' => 'some url'
        );

        $emailTemplate = $this->getModelMock('core/email_template', array('sendTransactional', 'getSentSuccess'));
        $emailTemplate->expects($this->once())->method('sendTransactional')->with(
            'template_id',
            'sales',
            'customer@example.com',
            'Customer Name',
            $templateVars,
            10
        );
        $emailTemplate->expects($this->once())->method('getSentSuccess')->willReturn(true);
        $this->replaceByMock('model', 'core/email_template', $emailTemplate);

        $model = $this->getModelMock('sheep_subscription/notification_service', array('_getEmailTemplateVariables'));
        $model->expects($this->once())->method('_getEmailTemplateVariables')->with($subscription)->willReturn($templateVars);

        $result = $model->sendSubscriptionEmail('template_id', $subscription);
        $this->assertTrue($result);
    }
}
