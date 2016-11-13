<?php

use Smajti1\Laravel\Exceptions\StepNotFoundException;
use Smajti1\Laravel\Wizard;

class WizardTest extends PHPUnit_Framework_TestCase
{
    protected $sessionKeyName;
    protected $wizardFirstStepKey;
    protected $steps;
    protected $wizard;

    public function setUp()
    {
        parent::setUp();

        $this->wizardFirstStepKey = 'first_step_key';
        $this->steps = [
            $this->wizardFirstStepKey => FirstTestStep::class,
            SecondTestStep::class,
        ];

        $this->wizard = new Wizard($this->steps, $this->sessionKeyName = 'test');
    }

    /** @test */
    public function wizard_test_basic_functions()
    {
        $this->assertTrue($this->wizard->limit() == count($this->steps));
        $this->assertTrue($this->wizard->hasNext());
        $this->assertFalse($this->wizard->hasPrev());
        $this->assertTrue(count($this->steps) == count($this->wizard->all()));

        $this->assertTrue($this->wizard->first()->key == $this->wizardFirstStepKey);
        $this->assertTrue($this->wizard->nextSlug() == SecondTestStep::$slug);

    }

    /** @test */
    public function wizard_test_steps()
    {
        $nextStep = $this->wizard->nextStep();
        $this->assertTrue($nextStep::$slug == SecondTestStep::$slug);

        $goBackToPrevStep = $this->wizard->prevStep();
        $this->assertTrue($goBackToPrevStep::$slug == FirstTestStep::$slug);

        $stepBySlug = $this->wizard->getBySlug(SecondTestStep::$slug);
        $this->assertTrue($stepBySlug::$slug == SecondTestStep::$slug);


        $this->setExpectedException(StepNotFoundException::class);
        $this->wizard->getBySlug('wrong_slug');
    }
}

class FirstTestStep extends \Smajti1\Laravel\Step
{

    public static $label = 'First step label';
    public static $slug = 'first-step';
    public static $view = '';

    public function process(\Illuminate\Http\Request $request)
    {
    }

    public function rules(\Illuminate\Http\Request $request = null)
    {
    }
}

class SecondTestStep extends \Smajti1\Laravel\Step
{

    public static $label = 'Second step label';
    public static $slug = 'second-step';
    public static $view = '';

    public function process(\Illuminate\Http\Request $request)
    {
    }

    public function rules(\Illuminate\Http\Request $request = null)
    {
    }
}