<?php

namespace Smajti1\Laravel;

use InvalidArgumentException;
use Smajti1\Laravel\Exceptions\StepNotFoundException;

class Wizard
{

    const SESSION_NAME = 'smajti1.wizard';
	/** @var array<int, Step> */
    protected $steps = [];
	/** @var int */
    protected $currentIndex = -1;
	/** @var string */
    protected $sessionKeyName = '';

    /**
	 * @param array<int|string, class-string<Step>>|array{} $steps
     * @throws StepNotFoundException
     */
    public function __construct(array $steps, string $sessionKeyName = '')
    {
        if (empty($steps)) {
            throw new StepNotFoundException();
        }

        $this->currentIndex = $index = 0;
        $naturalNumber = 1;
        foreach ($steps as $key => $stepClassName) {
            $newStep = $this->createStepClass($stepClassName, $naturalNumber, $key, $index);
            $this->steps[$index] = $newStep;
            $index++;
            $naturalNumber++;
        }

        $this->sessionKeyName = self::SESSION_NAME . '.' . $sessionKeyName;
        if (function_exists('view')) {
            view()->share(['wizard' => $this]);
        }
    }

	/**
	 * @param class-string<Step> $stepClassName
	 * @param string|int $key
	 */
    protected function createStepClass($stepClassName, int $naturalNumber, $key, int $index): Step
    {
		return new $stepClassName($naturalNumber, $key, $index, $this);
    }

    public function prevStep(): ?Step
    {
        if ($this->hasPrev()) {
            return $this->get($this->currentIndex - 1);
        }
        return null;
    }

    public function hasPrev(): bool
    {
        return $this->currentIndex > 0 && isset($this->steps[$this->currentIndex - 1]);
    }

    /**
     * @throws StepNotFoundException
     */
    protected function get(int $index, bool $moveCurrentIndex = true): Step
    {
        if (!isset($this->steps[$index])) {
            throw new StepNotFoundException();
        }
        if ($moveCurrentIndex) {
            $this->currentIndex = $index;
        }
        return $this->steps[$index];
    }

    public function prevSlug(): ?string
    {
        if ($this->hasPrev()) {
            $prevSlug = $this->get($this->currentIndex - 1, false);
            return $prevSlug::$slug;
        }
        return null;
    }

    public function nextStep(): ?Step
    {
        if ($this->hasNext()) {
            return $this->get($this->currentIndex + 1);
        }
        return null;
    }

    public function hasNext(): bool
    {
        return $this->currentIndex < $this->limit() && isset($this->steps[$this->currentIndex + 1]);
    }

    public function limit(): int
    {
        return count($this->steps);
    }

    public function nextSlug(): ?string
    {
        if ($this->hasNext()) {
            $nextStep = $this->get($this->currentIndex + 1, false);
            return $nextStep::$slug;
        }
        return null;
    }

    /**
     * @throws StepNotFoundException
     */
    public function getBySlug(string $slug = ''): Step
    {
        $index = 0;
        foreach ($this->steps as $key => $step) {
            if ($step::$slug == $slug) {
                $this->currentIndex = $index;
                return $step;
            }
            $index++;
        }
        throw new StepNotFoundException();
    }

    public function first(): Step
    {
        return $this->steps[0];
    }

    public function firstOrLastProcessed(int $moveSteps = 0): Step
    {
        $lastProcessed = $this->lastProcessedIndex() ?: 0;
        $lastProcessed += $moveSteps;
        $this->currentIndex = $lastProcessed;
        return $this->steps[$lastProcessed];
    }

    /**
     * @deprecated
     */
    public function lastProcessed(): ?bool
    {
		$last_processed_index = $this->lastProcessedIndex();
		return $last_processed_index === null ? null : (bool) $last_processed_index;
    }

    public function lastProcessedIndex(): ?int
    {
        $data = $this->data();
        if ($data) {
			return $data['lastProcessed'] ?? null;
        }
        return null;
    }

	/**
	 * @param array<mixed>|array{}|null $data
	 * @return array<string|int, mixed>|array{}
	 */
    public function data($data = null): array
    {
        $default = [];
        if (!function_exists('session')) {
            return $default;
        }
        if (is_array($data)) {
            $data['lastProcessed'] = $this->currentIndex;
            session([$this->sessionKeyName => $data]);
        }
        return session($this->sessionKeyName, $default);
    }

	/**
	 * @param string|int $key
	 */
    public function dataHas($key): bool
    {
        $data = $this->data();
        return isset($data[$key]);
    }

	/**
	 * @param string|int $key
	 * @return array<mixed>
	 */
    public function dataGet($key)
    {
        $data = $this->data();
        return $data[$key];
    }

	/**
	 * @param string|int $key
	 * @return array<mixed>
	 */
    public function dataStep(Step $step, $key): array
    {
        $data = $this->data();
		return $data[$step::$slug][$key] ?? [];
    }

	/**
	 * @return array<int, Step>
	 */
    public function all(): array
    {
        return $this->steps;
    }

	/**
	 * @param class-string<Step> $newStepClass
	 * @param string|int $key
	 */
    public function replaceStep(int $index, string $newStepClass, $key): Step
    {
        $step = $this->steps[$index];
        $step->clearData();

        $this->steps[$index] = $this->createStepClass($newStepClass, $index + 1, $key, $index);

        return $this->steps[$index];
    }

	/**
	 * @param class-string<Step> $stepClass
	 * @param string|int $key
	 */
    public function appendStep(string $stepClass, $key): Step
    {
        $newIndex = count($this->steps);
        $this->steps[] = $this->createStepClass($stepClass, $newIndex + 1, $key, $newIndex);

        return $this->steps[$newIndex];
    }

	/**
	 * @param string|int $key
	 * @param class-string<Step> $stepClass
	 */
    public function insertStep(int $index, string $stepClass, $key): Step
    {
        if ($index < 0) {
            throw new InvalidArgumentException('Cannot set index below zero!');
        }
        $stepsCount = count($this->steps);
        if ($index >= $stepsCount) {
            return $this->appendStep($stepClass, $key);
        }

        for ($i = $stepsCount; $i > $index; $i--) {
            $this->steps[$i] = $this->steps[$i - 1];
            $this->steps[$i]->index++;
            $this->steps[$i]->number++;
        }

        $this->steps[$index] = $this->createStepClass($stepClass, $index + 1, $key, $index);

        return $this->steps[$index];
    }

    public function destroyStep(int $index): void
    {
        $step = $this->get($index);
        $step->clearData();

        $stepsCount = count($this->steps);
        for ($i = $index + 1; $i < $stepsCount; $i++) {
            $this->steps[$i]->index--;
            $this->steps[$i]->number--;
        }

        unset($this->steps[$index]);
        $this->steps = array_values($this->steps);
    }

    public function clearProgress(): void
    {
        $this->currentIndex = count($this->steps) > 0 ? 0 : -1;
        $this->clearData();
    }

    public function clearData(): void
    {
        $this->data([]);
    }
}
