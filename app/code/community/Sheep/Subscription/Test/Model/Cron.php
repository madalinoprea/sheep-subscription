<?php

/**
 * Class Sheep_Subscription_Test_Model_Cron
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers   Sheep_Subscription_Model_Cron
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Model_Cron extends EcomDev_PHPUnit_Test_Case
{
    /** @var Sheep_Subscription_Model_Cron $model */
    protected $model;

    protected function setUp()
    {
        $this->model = Mage::getModel('sheep_subscription/cron');
    }


    /**
     * @covers Sheep_Subscription_Model_Cron::getMessagesPerIteration
     */
    public function testGetMessagesPerIteration()
    {
        $actual = $this->model->getMessagesPerIteration();
        $this->assertGreaterThan(0, $actual);
    }


    /**
     * @covers Sheep_Subscription_Model_Cron::getMessageTimeout
     */
    public function testGetMessageTimeout()
    {
        $actual = $this->model->getMessageTimeout();
        $this->assertGreaterThan(0, $actual);
    }

    /**
     * @covers Sheep_Subscription_Model_Cron::getService
     */
    public function testGetService()
    {
        $service = $this->model->getService();
        $this->assertNotNull($service);
        $this->assertInstanceOf('Sheep_Subscription_Model_Service', $service);
    }


    /**
     * @covers Sheep_Subscription_Model_Cron::checkRenewals
     * @throws Exception
     */
    public function testCheckRenewals()
    {
        $queueMock = $this->getMock('Zend_Queue', array('send'), array(), '', false);
        $queueMock->expects($this->at(0))->method('send')->with($this->stringContains('100'));
        $queueMock->expects($this->at(1))->method('send')->with($this->stringContains('101'));

        $helperMock = $this->getHelperMock('sheep_queue', array('getQueue'));
        $helperMock->expects($this->once())->method('getQueue')->with(Sheep_Subscription_Model_Cron::RENEWAL_PROCESSING_QUEUE)->willReturn($queueMock);
        $this->replaceByMock('helper', 'sheep_queue', $helperMock);

        /** @var Sheep_Subscription_Model_Resource_Renewal_Collection $renewals */
        $renewals = $this->getResourceModelMock('sheep_subscription/renewal_collection', array('load'));
        $firstRenewal = $this->getModelMock('sheep_subscription/renewal', array('getId', 'getDate', 'setStatus', 'save'));
        $firstRenewal->expects($this->any())->method('getId')->willReturn(100);
        $firstRenewal->expects($this->any())->method('getDate')->willReturn('2015-12-07');
        $firstRenewal->expects($this->once())->method('setStatus')->with(Sheep_Subscription_Model_Renewal::STATUS_PROCESSING);
        $firstRenewal->expects($this->once())->method('save');
        $renewals->addItem($firstRenewal);

        $secondRenewal = $this->getModelMock('sheep_subscription/renewal', array('getId', 'getDate', 'setStatus', 'save'));
        $secondRenewal->expects($this->any())->method('getId')->willReturn(101);
        $secondRenewal->expects($this->any())->method('getDate')->willReturn('2015-12-08');
        $secondRenewal->expects($this->once())->method('setStatus')->with(Sheep_Subscription_Model_Renewal::STATUS_PROCESSING);
        $secondRenewal->expects($this->once())->method('save');
        $renewals->addItem($secondRenewal);

        $serviceMock = $this->getModelMock('sheep_subscription/service', array('getPendingRenewals'));
        $serviceMock->expects($this->once())->method('getPendingRenewals')->willReturn($renewals);

        $model = $this->getModelMock('sheep_subscription/cron', array('getService'));
        $model->expects($this->once())->method('getService')->willReturn($serviceMock);

        $actual = $model->checkRenewals();
        $this->assertNotNull($actual);
        $this->assertContains('2 renewals moved to processing', $actual);
    }


    /**
     * @covers Sheep_Subscription_Model_Cron::processRenewals
     */
    public function testProcessRenewals()
    {
        $ZendQueueMessage = $this->getMockClass('Zend_Queue_Message', array('setQueue'), array(), '', true);
        $failedRenewalMessage = new $ZendQueueMessage(array('data' => array('message_id' => '111102', 'body' => json_encode(array('renewal_id' => 100)))));
        $correctRenewalMessage = new $ZendQueueMessage(array('data' => array('message_id' => '111102', 'body' => json_encode(array('renewal_id' => 101)))));
        $messages = array($failedRenewalMessage, $correctRenewalMessage);

        $queueMock = $this->getMock('Zend_Queue', array('receive', 'deleteMessage'), array(), '', false);
        $queueMock->expects($this->once())->method('receive')->with(10, 3600)->willReturn($messages);
        $queueMock->expects($this->once())->method('deleteMessage')->with($correctRenewalMessage);

        $helperMock = $this->getHelperMock('sheep_queue', array('getQueue'));
        $helperMock->expects($this->once())->method('getQueue')->with(Sheep_Subscription_Model_Cron::RENEWAL_PROCESSING_QUEUE)->willReturn($queueMock);
        $this->replaceByMock('helper', 'sheep_queue', $helperMock);

        $renewal = $this->getModelMock('sheep_subscription/renewal', array('getId', 'getStatus'));
        $renewal->expects($this->any())->method('getId')->willReturn(100);
        $renewal->expects($this->any())->method('getStatus')->willReturn(Sheep_Subscription_Model_Renewal::STATUS_PROCESSING);

        $renewalMock = $this->getModelMock('sheep_subscription/renewal', array('load'));
        $renewalMock->expects($this->at(0))->method('load')->with(100)->willReturn($renewal);
        $renewalMock->expects($this->at(1))->method('load')->with(101)->willReturn($renewal);
        $this->replaceByMock('model', 'sheep_subscription/renewal', $renewalMock);

        $serviceMock = $this->getModelMock('sheep_subscription/service', array('processRenewal'));
        $serviceMock->expects($this->at(0))->method('processRenewal')->willReturn(false);
        $serviceMock->expects($this->at(1))->method('processRenewal')->willReturn(true);

        /** @var Sheep_Subscription_Model_Cron $model */
        $model = $this->getModelMock('sheep_subscription/cron', array('getService', 'getMessagesPerIteration', 'getMessageTimeout'));
        $model->expects($this->any())->method('getMessagesPerIteration')->willReturn(10);
        $model->expects($this->any())->method('getMessageTimeout')->willReturn(3600);
        $model->expects($this->once())->method('getService')->willReturn($serviceMock);

        $actual = $model->processRenewals();
        $this->assertNotEmpty($actual);
        $this->assertContains('1 created orders, 2 processed messages', $actual);
    }


    /**
     * Verifies that renewals that cannot be found from queue message are not processed and they are kept in queue to be
     * manually verified.
     * @covers Sheep_Subscription_Model_Cron::processRenewals
     */
    public function testProcessRenewalsWithMissingRenewal()
    {
        $ZendQueueMessage = $this->getMockClass('Zend_Queue_Message', array('setQueue'), array(), '', true);
        $message= new $ZendQueueMessage(array('data' => array('message_id' => '111102', 'body' => json_encode(array('renewal_id' => 100)))));
        $messages = array($message);

        $queueMock = $this->getMock('Zend_Queue', array('receive', 'deleteMessage'), array(), '', false);
        $queueMock->expects($this->once())->method('receive')->with(10, 3600)->willReturn($messages);
        $queueMock->expects($this->never())->method('deleteMessage');

        $helperMock = $this->getHelperMock('sheep_queue', array('getQueue'));
        $helperMock->expects($this->once())->method('getQueue')->with(Sheep_Subscription_Model_Cron::RENEWAL_PROCESSING_QUEUE)->willReturn($queueMock);
        $this->replaceByMock('helper', 'sheep_queue', $helperMock);

        $renewal = $this->getModelMock('sheep_subscription/renewal', array('getId', 'getStatus'));
        $renewal->expects($this->any())->method('getId')->willReturn(null);
        $renewal->expects($this->any())->method('getStatus')->willReturn(Sheep_Subscription_Model_Renewal::STATUS_PROCESSING);

        $renewalMock = $this->getModelMock('sheep_subscription/renewal', array('load'));
        $renewalMock->expects($this->at(0))->method('load')->with(100)->willReturn($renewal);
        $this->replaceByMock('model', 'sheep_subscription/renewal', $renewalMock);

        $serviceMock = $this->getModelMock('sheep_subscription/service', array('processRenewal'));
        $serviceMock->expects($this->never())->method('processRenewal');

        /** @var Sheep_Subscription_Model_Cron $model */
        $model = $this->getModelMock('sheep_subscription/cron', array('getService', 'getMessagesPerIteration', 'getMessageTimeout'));
        $model->expects($this->any())->method('getMessagesPerIteration')->willReturn(10);
        $model->expects($this->any())->method('getMessageTimeout')->willReturn(3600);
        $model->expects($this->once())->method('getService')->willReturn($serviceMock);

        $actual = $model->processRenewals();
        $this->assertNotEmpty($actual);
        $this->assertContains('0 created orders, 1 processed messages', $actual);
    }

    /**
     * Verifies that for renewals that are not in processing status are not payed and they are deleted from queue
     * @covers Sheep_Subscription_Model_Cron::processRenewals
     */
    public function testProcessRenewalsWithInvalidStatus()
    {
        $ZendQueueMessage = $this->getMockClass('Zend_Queue_Message', array('setQueue'), array(), '', true);
        $message= new $ZendQueueMessage(array('data' => array('message_id' => '111102', 'body' => json_encode(array('renewal_id' => 100)))));
        $messages = array($message);

        $queueMock = $this->getMock('Zend_Queue', array('receive', 'deleteMessage'), array(), '', false);
        $queueMock->expects($this->once())->method('receive')->with(10, 3600)->willReturn($messages);
        $queueMock->expects($this->once())->method('deleteMessage')->with($message);

        $helperMock = $this->getHelperMock('sheep_queue', array('getQueue'));
        $helperMock->expects($this->once())->method('getQueue')->with(Sheep_Subscription_Model_Cron::RENEWAL_PROCESSING_QUEUE)->willReturn($queueMock);
        $this->replaceByMock('helper', 'sheep_queue', $helperMock);

        $renewal = $this->getModelMock('sheep_subscription/renewal', array('getId', 'getStatus'));
        $renewal->expects($this->any())->method('getId')->willReturn(100);
        $renewal->expects($this->any())->method('getStatus')->willReturn(Sheep_Subscription_Model_Renewal::STATUS_PAYED);

        $renewalMock = $this->getModelMock('sheep_subscription/renewal', array('load'));
        $renewalMock->expects($this->at(0))->method('load')->with(100)->willReturn($renewal);
        $this->replaceByMock('model', 'sheep_subscription/renewal', $renewalMock);

        $serviceMock = $this->getModelMock('sheep_subscription/service', array('processRenewal'));
        $serviceMock->expects($this->never())->method('processRenewal');

        /** @var Sheep_Subscription_Model_Cron $model */
        $model = $this->getModelMock('sheep_subscription/cron', array('getService', 'getMessagesPerIteration', 'getMessageTimeout'));
        $model->expects($this->any())->method('getMessagesPerIteration')->willReturn(10);
        $model->expects($this->any())->method('getMessageTimeout')->willReturn(3600);
        $model->expects($this->once())->method('getService')->willReturn($serviceMock);

        $actual = $model->processRenewals();
        $this->assertNotEmpty($actual);
        $this->assertContains('0 created orders, 1 processed messages', $actual);
    }
}
