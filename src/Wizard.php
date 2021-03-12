<?php

namespace Smajti1\Laravel;

use Smajti1\Laravel\Exceptions\StepNotFoundException;

class Wizard
{

    const SESSION_NAME = 'smajti1.wizard';
    protected $steps = [];
    protected $currentIndex = -1;
    protected $sessionKeyName = '';

    /**
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

    protected function createStepClass($stepClassName, int $naturalNumber, $key, int $index): Step
    {
        $step = new $stepClassName($naturalNumber, $key, $index, $this);
        return $step;
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
        return $this->lastProcessedIndex();
    }

    public function lastProcessedIndex(): ?int
    {
        $data = $this->data();
        if ($data) {
            $lastProcessed = isset($data['lastProcessed']) ? $data['lastProcessed'] : null;
            return $lastProcessed;
        }
        return null;
    }

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

    public function dataHas($key): bool
    {
        $data = $this->data();
        return isset($data[$key]);
    }

    public function dataGet($key)
    {
        $data = $this->data();
        return $data[$key];
    }

    public function dataStep(Step $step, $key): array
    {
        $data = $this->data();
        $stepData = $data[$step::$slug][$key] ?? [];
        return $stepData;
    }

    public function all(): array
    {
        return $this->steps;
    }

    public function replaceStep(int $index, string $new_step_class, string $key): Step
    {
        $step = $this->steps[$index];
        $step->clearData();

        $this->steps[$index] = $this->createStepClass($new_step_class, $index + 1, $key, $index);

        return $this->steps[$index];
    }

    public function appendStep(string $step_class, string $key): Step
    {
        $new_index = count($this->steps);
        $this->steps[] = $this->createStepClass($step_class, $new_index + 1, $key, $new_index);

        return $this->steps[$new_index];
    }

    public function insertStep(int $index, string $step_class, string $key): Step
    {
        if ($index >= count($this->steps) || count($this->steps) === 0) {
            return $this->appendStep($step_class, $key);
        }

        for ($i = count($this->steps); $i > $index; $i--) {
            $this->steps[$i] = $this->steps[$i - 1];
            $this->steps[$i]->index++;
            $this->steps[$i]->number++;
        }

        $this->steps[$index] = $this->createStepClass($step_class, $index + 1, $key, $index);

        return $this->steps[$index];
    }

    public function destroyStep(int $index): void
    {
        $step = $this->get($index);
        $step->clearData();

        for ($i=$index+1; $i < count($this->steps); $i++) {
            $this->steps[$i]->index--;
            $this->steps[$i]->number--;
        }

        unset($this->steps[$index]);
        $this->steps = array_values($this->steps);
    }

    public function clearProgress()
    {
        $this->currentIndex = count($this->steps) > 0 ? 0 : -1;
        $this->clearData();
    }

    public function clearData()
    {
        $this->data([]);
    }
}