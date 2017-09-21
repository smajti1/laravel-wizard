<?php

use Smajti1\Laravel\Exceptions\StepNotFoundException;
use Smajti1\Laravel\Step;
use Smajti1\Laravel\Wizard;

class WizardTest extends PHPUnit\Framework\TestCase
{
    protected $sessionKeyName;
    protected $wizardFirstStepKey;
    protected $steps;
    protected $wizard;
    protected $firstTestStepClass;
    protected $secondTestStepClass;

    public function __construct()
    {
        parent::__construct();
        $this->firstTestStepClass = $this->createMock(Step::class);
        $this->firstTestStepClass::$label = 'First step label';
        $this->firstTestStepClass::$slug = 'first-step';
        $this->firstTestStepClass::$view = '';

        $this->secondTestStepClass = $this->createMock(Step::class);
        $this->secondTestStepClass::$label = 'Second step label';
        $this->secondTestStepClass::$slug = 'second-step';
        $this->secondTestStepClass::$view = '';

        $this->wizardFirstStepKey = 'first_step_key';
        $this->steps = [
            $this->wizardFirstStepKey => get_class($this->firstTestStepClass),
            get_class($this->secondTestStepClass),
        ];
        $this->sessionKeyName = 'test';
        $this->wizard = $this->getMockBuilder(Wizard::class)->setConstructorArgs([$this->steps, $this->sessionKeyName])->setMethods(null)->getMock();
    }

    public function testWizardTestBasicFunctions()
    {
        $this->assertTrue($this->wizard->limit() == count($this->steps));
        $this->assertTrue($this->wizard->hasNext());
        $this->assertFalse($this->wizard->hasPrev());
        $this->assertTrue(count($this->steps) == count($this->wizard->all()));
        $this->assertTrue($this->wizard->first()->key == $this->wizardFirstStepKey);
        $this->assertTrue($this->wizard->nextSlug() == $this->secondTestStepClass::$slug);
    }

    public function testWizardTestSteps()
    {
        $nextStep = $this->wizard->nextStep();
        $this->assertTrue($nextStep::$slug == $this->secondTestStepClass::$slug);

        $goBackToPrevStep = $this->wizard->prevStep();
        $this->assertTrue($goBackToPrevStep::$slug == $this->firstTestStepClass::$slug);

        $stepBySlug = $this->wizard->getBySlug($this->secondTestStepClass::$slug);
        $this->assertTrue($stepBySlug::$slug == $this->secondTestStepClass::$slug);
    }

    public function testWizardGetNotExistingStep()
    {
        $this->expectException(StepNotFoundException::class);
        $this->wizard->getBySlug('wrong_slug');
    }
}
