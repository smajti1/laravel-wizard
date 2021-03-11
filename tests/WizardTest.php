<?php

declare(strict_types=1);

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
    protected $fourthTestStepClass;
    protected $wizardThirdStepKey;
    protected $wizardFourthStepKey;

    public function __construct()
    {
        parent::__construct();
        $this->firstTestStepClass = $this->createPartialMock(Step::class, ['process',]);
        $this->firstTestStepClass::$label = 'First step label';
        $this->firstTestStepClass::$slug = 'first-step';
        $this->firstTestStepClass::$view = '';

        $this->secondTestStepClass = $this->createPartialMock(Step::class, ['process',]);
        $this->secondTestStepClass::$label = 'Second step label';
        $this->secondTestStepClass::$slug = 'second-step';
        $this->secondTestStepClass::$view = '';

        $this->thirdTestStepClass = $this->createPartialMock(Step::class, ['process',]);
        $this->secondTestStepClass::$label = 'Third step label';
        $this->secondTestStepClass::$slug = 'third-step';
        $this->secondTestStepClass::$view = '';

        $this->fourthTestStepClass = $this->createPartialMock(Step::class, ['process',]);
        $this->fourthTestStepClass::$label = 'Fourth step label';
        $this->fourthTestStepClass::$slug = 'fourth-step';
        $this->fourthTestStepClass::$view = '';

        $this->wizardFirstStepKey = 'first_step_key';
        $this->wizardThirdStepKey = 'step_key_third';
        $this->wizardFourthStepKey = 'fourth_step_key';
        $this->steps = [
            $this->wizardFirstStepKey => get_class($this->firstTestStepClass),
            get_class($this->secondTestStepClass),
            $this->wizardThirdStepKey => get_class($this->thirdTestStepClass),
        ];
        $this->sessionKeyName = 'test';
        $this->wizard = $this->createPartialMock(Wizard::class, [
            'createStepClass',
            'lastProcessedIndex',
        ]);

        $this->wizard_reflection = new ReflectionClass(Wizard::class);
    }

    public function testConstructor(): void
    {
        $this->wizard->expects(self::exactly(3))
            ->method('createStepClass');
        $this->wizard->__construct($this->steps);
    }

    public function testConstructorEmptySteps(): void
    {
        $this->expectException(StepNotFoundException::class);
        $this->wizard->__construct([]);
    }

    public function testCreateStepClass(): void
    {
        $testStepClassName = 'TestStepClassName';
        $this->getMockForAbstractClass(Step::class, [], $testStepClassName, false);

        $method = $this->wizard_reflection->getMethod('createStepClass');
        $method->setAccessible(true);

        $result = $method->invoke($this->wizard, $testStepClassName, 1, 'test_key', 2);
        self::assertInstanceOf($testStepClassName, $result);
        self::assertInstanceOf(Step::class, $result);
    }

    public function testPrevStep(): void
    {
        $this->wizard->__construct($this->steps);
        self::assertNull($this->wizard->prevStep());
        $this->wizard->nextStep();
        self::assertInstanceOf(Step::class, $this->wizard->prevStep());
    }

    public function testHasStep(): void
    {
        $this->wizard->__construct($this->steps);
        self::assertFalse($this->wizard->hasPrev());
        $this->wizard->nextStep();
        self::assertTrue($this->wizard->hasPrev());
    }

    public function testGetNotExistingStep(): void
    {
        $this->expectException(StepNotFoundException::class);

        $method = $this->wizard_reflection->getMethod('get');
        $method->setAccessible(true);

        $method->invoke($this->wizard, -1);
    }

    public function testPrevSlug(): void
    {
        $this->wizard->__construct($this->steps);
        self::assertNull($this->wizard->prevSlug());
        $this->wizard->nextStep();
        self::assertEquals($this->firstTestStepClass::$slug, $this->wizard->prevSlug());
    }

    public function testNextStep(): void
    {
        $this->wizard->__construct($this->steps);
        self::assertInstanceOf(Step::class, $this->wizard->nextStep());
        $this->wizard->nextStep();
        self::assertNull($this->wizard->nextStep());
    }

    public function testNextSlug(): void
    {
        $this->wizard->__construct($this->steps);
        self::assertEquals($this->secondTestStepClass::$slug, $this->wizard->nextSlug());
        $this->wizard->nextStep();
        $this->wizard->nextStep();
        self::assertNull($this->wizard->nextSlug());
    }

    public function testGetBySlugNotExistingStep(): void
    {
        $this->expectException(StepNotFoundException::class);
        $this->wizard->getBySlug('wrong_slug');
    }

    public function testFirst(): void
    {
        $this->wizard->__construct($this->steps);
        $result = $this->wizard->first();
        self::assertEquals($this->firstTestStepClass::$slug, $result::$slug);
    }

    public function testFirstOrLastProcessed(): void
    {
        $this->wizard->__construct($this->steps);
        $this->wizard->expects(self::once())
            ->method('lastProcessedIndex')
            ->willReturn(1);
        $allSteps = $this->wizard->all();
        $result = $this->wizard->firstOrLastProcessed();
        self::assertEquals($allSteps[1], $result);
    }

    public function testLastProcessedIndex(): void
    {
        $wizard = $this->createPartialMock(Wizard::class, ['data',]);
        $wizard->expects(self::once())
            ->method('data')
            ->willReturn(['lastProcessed' => 1]);
        self::assertEquals(1, $wizard->lastProcessedIndex());
    }

    public function testLastProcessedIndexWithoutData(): void
    {
        $wizard = $this->createPartialMock(Wizard::class, ['data',]);
        $wizard->expects(self::once())
            ->method('data')
            ->willReturn([]);
        self::assertNull($wizard->lastProcessedIndex());
    }

    public function testWizardTestSteps(): void
    {
        $this->wizard->__construct($this->steps);
        $nextStep = $this->wizard->nextStep();
        self::assertEquals($nextStep::$slug, $this->secondTestStepClass::$slug);

        $goBackToPrevStep = $this->wizard->prevStep();
        self::assertEquals($goBackToPrevStep::$slug, $this->firstTestStepClass::$slug);

        $stepBySlug = $this->wizard->getBySlug($this->secondTestStepClass::$slug);
        self::assertEquals($stepBySlug::$slug, $this->secondTestStepClass::$slug);
    }

    public function testWizardAppendSteps(): void
    {
        $this->wizard = $this->createPartialMock(Wizard::class, []);
        $this->wizard->__construct($this->steps);

        $result = $this->wizard->appendStep($this->fourthTestStepClass::class, $this->wizardFourthStepKey);

        self::assertEquals(4, count($this->wizard->all()));
        self::assertEquals($this->fourthTestStepClass::$slug, $result::$slug);
        self::assertEquals($this->wizardFourthStepKey, $this->wizard->all()[3]->key);
    }

    public function testWizardInsertSteps(): void
    {
        $this->wizard = $this->createPartialMock(Wizard::class, []);
        $this->wizard->__construct($this->steps);

        $result = $this->wizard->insertStep(1, $this->fourthTestStepClass::class, $this->wizardFourthStepKey);

        self::assertEquals(4, count($this->wizard->all()));
        self::assertEquals($this->fourthTestStepClass::$slug, $result::$slug);
        self::assertEquals($this->wizardFourthStepKey, $this->wizard->all()[1]->key);
    }

    public function testWizardReplaceSteps(): void
    {
        $this->wizard = $this->createPartialMock(Wizard::class, []);
        $this->wizard->__construct($this->steps);

        $result = $this->wizard->replaceStep(1, $this->fourthTestStepClass::class, $this->wizardFourthStepKey);

        self::assertEquals(3, count($this->wizard->all()));
        self::assertEquals($this->fourthTestStepClass::$slug, $result::$slug);
        self::assertEquals($this->wizardFourthStepKey, $this->wizard->all()[1]->key);
    }

    public function testWizardDestroySteps(): void
    {
        $this->wizard = $this->createPartialMock(Wizard::class, []);
        $this->wizard->__construct($this->steps);

        $this->wizard->destroyStep(1);

        self::assertEquals(2, count($this->wizard->all()));
        self::assertEquals($this->wizardThirdStepKey, $this->wizard->all()[1]->key);
    }
}