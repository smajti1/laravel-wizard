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
    protected $wizard_reflection;
    protected $thirdTestStepClass;
    protected $wizardThirdStepKey;

    public function __construct()
    {
        parent::__construct();
        $this->firstTestStepClass = $this->getMockBuilder(Step::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'process',
            ])
            ->getMock();
        $this->firstTestStepClass::$label = 'First step label';
        $this->firstTestStepClass::$slug = 'first-step';
        $this->firstTestStepClass::$view = '';

        $this->secondTestStepClass = $this->getMockBuilder(Step::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'process',
            ])
            ->getMock();
        $this->secondTestStepClass::$label = 'Second step label';
        $this->secondTestStepClass::$slug = 'second-step';
        $this->secondTestStepClass::$view = '';

        $this->thirdTestStepClass = $this->getMockBuilder(Step::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'process',
            ])
            ->getMock();
        $this->secondTestStepClass::$label = 'Third step label';
        $this->secondTestStepClass::$slug = 'third-step';
        $this->secondTestStepClass::$view = '';

        $this->wizardFirstStepKey = 'first_step_key';
        $this->wizardThirdStepKey = 'step_key_third';
        $this->steps = [
            $this->wizardFirstStepKey => get_class($this->firstTestStepClass),
            get_class($this->secondTestStepClass),
            $this->wizardThirdStepKey => get_class($this->thirdTestStepClass),
        ];
        $this->sessionKeyName = 'test';
        $this->wizard = $this->getMockBuilder(Wizard::class)
            ->disableOriginalConstructor()
            ->setMethods(['createStepClass', 'lastProcessedIndex'])
            ->getMock();

        $this->wizard_reflection = new \ReflectionClass(Wizard::class);
    }

    public function testConstructor()
    {
        $this->wizard->expects($this->exactly(3))
            ->method('createStepClass');
        $this->wizard->__construct($this->steps);
    }

    public function testConstructorEmptySteps()
    {
        $this->expectException(StepNotFoundException::class);
        $this->wizard->__construct([]);
    }

    public function testCreateStepClass()
    {
        $testStepClassName = 'TestStepClassName';
        $this->getMockForAbstractClass(Step::class, [], $testStepClassName, false);

        $method = $this->wizard_reflection->getMethod('createStepClass');
        $method->setAccessible(true);

        $result = $method->invoke($this->wizard, $testStepClassName, 1, 'test_key', 2);
        $this->assertInstanceOf($testStepClassName, $result);
        $this->assertInstanceOf(Step::class, $result);
    }

    public function testPrevStep()
    {
        $this->wizard->__construct($this->steps);
        $this->assertNull($this->wizard->prevStep());
        $this->wizard->nextStep();
        $this->assertInstanceOf(Step::class, $this->wizard->prevStep());
    }

    public function testHasStep()
    {
        $this->wizard->__construct($this->steps);
        $this->assertFalse($this->wizard->hasPrev());
        $this->wizard->nextStep();
        $this->assertTrue($this->wizard->hasPrev());
    }

    public function testGetNotExistingStep()
    {
        $this->expectException(StepNotFoundException::class);

        $method = $this->wizard_reflection->getMethod('get');
        $method->setAccessible(true);

        $method->invoke($this->wizard, -1);
    }

    public function testPrevSlug()
    {
        $this->wizard->__construct($this->steps);
        $this->assertNull($this->wizard->prevSlug());
        $this->wizard->nextStep();
        $this->assertEquals($this->firstTestStepClass::$slug, $this->wizard->prevSlug());
    }

    public function testNextStep()
    {
        $this->wizard->__construct($this->steps);
        $this->assertInstanceOf(Step::class, $this->wizard->nextStep());
        $this->wizard->nextStep();
        $this->assertNull($this->wizard->nextStep());
    }

    public function testNextSlug()
    {
        $this->wizard->__construct($this->steps);
        $this->assertEquals($this->secondTestStepClass::$slug, $this->wizard->nextSlug());
        $this->wizard->nextStep();
        $this->wizard->nextStep();
        $this->assertNull($this->wizard->nextSlug());
    }

    public function testGetBySlugNotExistingStep()
    {
        $this->expectException(StepNotFoundException::class);
        $this->wizard->getBySlug('wrong_slug');
    }

    public function testFirst()
    {
        $this->wizard->__construct($this->steps);
        $result = $this->wizard->first();
        $this->assertEquals($this->firstTestStepClass::$slug, $result::$slug);
    }

    public function testFirstOrLastProcessed()
    {
        $this->wizard->__construct($this->steps);
        $this->wizard->expects($this->once())
            ->method('lastProcessedIndex')
            ->willReturn(1);
        $allSteps = $this->wizard->all();
        $result = $this->wizard->firstOrLastProcessed();
        $this->assertEquals($allSteps[1], $result);
    }

    public function testLastProcessedIndex()
    {
        $wizard = $this->getMockBuilder(Wizard::class)
            ->disableOriginalConstructor()
            ->setMethods(['data'])
            ->getMock();
        $wizard->expects($this->once())
            ->method('data')
            ->willReturn(['lastProcessed' => 1]);
        $this->assertEquals($wizard->lastProcessedIndex(), 1);
    }

    public function testLastProcessedIndexWithoutData()
    {
        $wizard = $this->getMockBuilder(Wizard::class)
            ->disableOriginalConstructor()
            ->setMethods(['data'])
            ->getMock();
        $wizard->expects($this->once())
            ->method('data')
            ->willReturn([]);
        $this->assertNull($wizard->lastProcessedIndex());
    }

    public function testWizardTestSteps()
    {
        $this->wizard->__construct($this->steps);
        $nextStep = $this->wizard->nextStep();
        $this->assertTrue($nextStep::$slug == $this->secondTestStepClass::$slug);

        $goBackToPrevStep = $this->wizard->prevStep();
        $this->assertTrue($goBackToPrevStep::$slug == $this->firstTestStepClass::$slug);

        $stepBySlug = $this->wizard->getBySlug($this->secondTestStepClass::$slug);
        $this->assertTrue($stepBySlug::$slug == $this->secondTestStepClass::$slug);
    }

}
