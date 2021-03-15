<?php

declare(strict_types=1);

namespace Smajti1\LaravelWizard\Test;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Smajti1\Laravel\Exceptions\StepNotFoundException;
use Smajti1\Laravel\Step;
use Smajti1\Laravel\Wizard;
use Smajti1\LaravelWizard\Test\Step\FirstDumpStep;
use Smajti1\LaravelWizard\Test\Step\SecondDumpStep;
use Smajti1\LaravelWizard\Test\Step\ThirdDumpStep;

class WizardTest extends TestCase
{
    protected $sessionKeyName;
    protected $wizardFirstStepKey;
    protected $steps;
    protected $wizard;
    protected $wizard_reflection;
    protected $wizardThirdStepKey;

    public function __construct()
    {
        parent::__construct();

        $this->wizardFirstStepKey = 'first_step_key';
        $this->wizardThirdStepKey = 'step_key_third';
        $this->steps = [
            $this->wizardFirstStepKey => FirstDumpStep::class,
            SecondDumpStep::class,
            $this->wizardThirdStepKey => ThirdDumpStep::class,
        ];
        $this->sessionKeyName = 'test';
        $this->wizard = $this->createPartialMock(Wizard::class, [
            'lastProcessedIndex',
        ]);

        $this->wizard_reflection = new ReflectionClass(Wizard::class);
    }

    public function testConstructor(): void
    {
        $wizard = $this->createPartialMock(Wizard::class, [
            'createStepClass',
        ]);
        $wizard->expects(self::exactly(3))
            ->method('createStepClass');
        $wizard->__construct($this->steps);
    }

    public function testConstructorEmptySteps(): void
	{
        $this->expectException(StepNotFoundException::class);
        $this->wizard->__construct([]);
    }

    public function testConstructorStepsInDifferentDirection(): void
    {
        $this->wizard->__construct([SecondDumpStep::class, ThirdDumpStep::class, FirstDumpStep::class]);
        $allSteps = $this->wizard->all();
        self::assertInstanceOf(SecondDumpStep::class, $allSteps[0]);
        self::assertInstanceOf(ThirdDumpStep::class, $allSteps[1]);
        self::assertInstanceOf(FirstDumpStep::class, $allSteps[2]);
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
        self::assertEquals(FirstDumpStep::$slug, $this->wizard->prevSlug());
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
        self::assertEquals(SecondDumpStep::$slug, $this->wizard->nextSlug());
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
        self::assertEquals(FirstDumpStep::$slug, $result::$slug);
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
        self::assertInstanceOf(FirstDumpStep::class, $allSteps[0]);
        self::assertInstanceOf(SecondDumpStep::class, $allSteps[1]);
        self::assertInstanceOf(ThirdDumpStep::class, $allSteps[2]);
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
        self::assertEquals($nextStep::$slug, SecondDumpStep::$slug);

        $goBackToPrevStep = $this->wizard->prevStep();
        self::assertEquals($goBackToPrevStep::$slug, FirstDumpStep::$slug);

        $stepBySlug = $this->wizard->getBySlug(SecondDumpStep::$slug);
        self::assertEquals($stepBySlug::$slug, SecondDumpStep::$slug);
    }

}
