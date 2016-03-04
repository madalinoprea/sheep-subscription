<?php

/**
 * Class Sheep_Subscription_Test_Block_Adminhtml_Type_Edit_Form
 *
 * @category Sheep
 * @package  Sheep_Subscription
 * @license  Copyright: Mario Oprea, 2015, All Rights reserved.
 * @link     https://moprea.ro
 *
 * @covers Sheep_Subscription_Block_Adminhtml_Type_Edit_Form
 * @codeCoverageIgnore
 */
class Sheep_Subscription_Test_Block_Adminhtml_Type_Edit_Form extends EcomDev_PHPUnit_Test_Case
{
    /** @var Sheep_Subscription_Block_Adminhtml_Type_Edit_Form $block */
    protected $block;

    protected function setUp()
    {
        parent::setUp();

        $layoutMock = $this->getModelMock('core/layout', array('createBlock'));
        $layoutMock->expects($this->any())->method('createBlock')->will($this->returnValue($this->getBlockMock('adminhtml/widget_form_element_dependence', array('getUrl'))));

        $this->block = $this->getBlockMock('sheep_subscription/adminhtml_type_edit_form', array('getUrl', 'getLayout'));
        $this->block->expects($this->any())->method('getLayout')->will($this->returnValue($layoutMock));
    }

    public function testGetModel()
    {
        $this->replaceRegistry('current_subscription_type', 'mock for current subscription type');
        $actual = $this->block->getModel();
        $this->assertEquals('mock for current subscription type', $actual);
    }

    public function testGetModelTitle()
    {
        $actual = $this->block->getModelTitle();
        $this->assertEquals('Subscription Type', $actual);
    }

    public function testPrepareForm()
    {
        $this->replaceRegistry('current_subscription_type', Mage::getModel('sheep_subscription/type'));
        EcomDev_Utils_Reflection::invokeRestrictedMethod($this->block, '_prepareForm');

        /** @var Varien_Data_Form $form */
        $form = $this->block->getForm();

        $this->assertNotNull($form);
        $this->assertEquals('edit_form', $form->getId());
        $this->assertEquals('post', $form->getMethod());

        $title = $form->getElement('title');
        $this->assertNotNull($title);
        $this->assertEquals('title', $title->getName());
        $this->assertEquals('Title', $title->getLabel());
        $this->assertEquals(true, $title->getRequired());
        $this->assertEquals('text', $title->getType());

        $status = $form->getElement('status');
        $this->assertNotNull($status);
        $this->assertEquals('status', $status->getName());
        $this->assertEquals('Status', $status->getLabel());
        $this->assertEquals(true, $status->getRequired());
        $this->assertEquals('select', $status->getType());
        $this->assertEquals(Sheep_Subscription_Model_Type::STATUS_ENABLED, $status->getValue());

        $periodCount = $form->getElement('period_count');
        $this->assertNotNull($periodCount);
        $this->assertEquals('period_count', $periodCount->getName());
        $this->assertEquals('Period Count', $periodCount->getLabel());
        $this->assertEquals(true, $periodCount->getRequired());
        $this->assertEquals('text', $periodCount->getType());
        $this->assertEquals(1, $periodCount->getValue());

        $periodUnit = $form->getElement('period_unit');
        $this->assertNotNull($periodUnit);
        $this->assertEquals('period_unit', $periodUnit->getName());
        $this->assertEquals('Period Unit', $periodUnit->getLabel());
        $this->assertEquals(true, $periodUnit->getRequired());
        $this->assertEquals('select', $periodUnit->getType());

        $isInfinite = $form->getElement('is_infinite');
        $this->assertNotNull($isInfinite);
        $this->assertEquals('is_infinite', $isInfinite->getName());
        $this->assertEquals('Is Infinite', $isInfinite->getLabel());
        $this->assertEquals(true, $isInfinite->getRequired());
        $this->assertEquals('select', $isInfinite->getType());
        $this->assertEquals(Sheep_Subscription_Model_Type::IS_INFINITE, $isInfinite->getValue());

        $occurrences = $form->getElement('occurrences');
        $this->assertNotNull($occurrences);
        $this->assertEquals('occurrences', $occurrences->getName());
        $this->assertEquals('Occurrences', $occurrences->getLabel());
        $this->assertEquals(true, $occurrences->getRequired());
        $this->assertEquals('text', $occurrences->getType());

        $hasTrial = $form->getElement('has_trial');
        $this->assertNotNull($hasTrial);
        $this->assertEquals('has_trial', $hasTrial->getName());
        $this->assertEquals('Has Trial', $hasTrial->getLabel());
        $this->assertEquals(true, $hasTrial->getRequired());
        $this->assertEquals('select', $hasTrial->getType());
        $this->assertEquals(Sheep_Subscription_Model_Type::WITHOUT_TRIAL, $hasTrial->getValue());


        $trialOccurrences = $form->getElement('trial_occurrences');
        $this->assertNotNull($trialOccurrences);
        $this->assertEquals('trial_occurrences', $trialOccurrences->getName());
        $this->assertEquals('Trial Occurrences', $trialOccurrences->getLabel());
        $this->assertEquals(true, $trialOccurrences->getRequired());
        $this->assertEquals('text', $trialOccurrences->getType());

        $initialFee = $form->getElement('initial_fee');
        $this->assertNotNull($initialFee);
        $this->assertEquals('initial_fee', $initialFee->getName());
        $this->assertEquals('Initial Fee', $initialFee->getLabel());
        $this->assertEquals(false, $initialFee->getRequired());
        $this->assertEquals('text', $initialFee->getType());
    }

}
