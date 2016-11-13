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
     * @param array $steps
     * @param string $sessionKeyName
     * @throws StepNotFoundException
     */
    public function __construct(array $steps, $sessionKeyName = '')
    {
        if (empty($steps))
            throw new StepNotFoundException();

        $this->currentIndex = $index = 0;
        $number = 1;
        foreach ($steps as $key => $step) {
            $newStep = new $step($number, $key, $index, $this);
            $this->steps[$index] = $newStep;
            $index++;
            $number++;
        }

        $this->sessionKeyName = self::SESSION_NAME . '.' . $sessionKeyName;
    }

    /**
     * @return mixed|null
     */
    public function prevStep()
    {
        if ($this->hasPrev()) {
            return $this->get($this->currentIndex - 1);
        }

        return null;
    }

    /**
     * @return bool
     */
    public function hasPrev()
    {
        return $this->currentIndex > 0 && isset($this->steps[$this->currentIndex - 1]);
    }

    /**
     * @param $index
     * @param bool $moveCurrentIndex
     * @return Step
     * @throws StepNotFoundException
     */
    protected function get($index, $moveCurrentIndex = true)
    {
        if (!isset($this->steps[$index])) {
            throw new StepNotFoundException();
        }

        if ($moveCurrentIndex) {
            $this->currentIndex = $index;
        }

        return $this->steps[$index];
    }

    /**
     * @return null|string
     */
    public function prevSlug()
    {
        if ($this->hasPrev()) {
            $prevSlug = $this->get($this->currentIndex - 1, false);

            return $prevSlug::$slug;
        }

        return null;
    }

    /**
     * @return Step|null
     */
    public function nextStep()
    {
        if ($this->hasNext()) {

            return $this->get($this->currentIndex + 1);
        }

        return null;
    }

    /**
     * @return bool
     */
    public function hasNext()
    {
        return $this->currentIndex < $this->limit() && isset($this->steps[$this->currentIndex + 1]);
    }

    /**
     * @return int
     */
    public function limit()
    {
        return count($this->steps);
    }

    /**
     * @return null|string
     */
    public function nextSlug()
    {
        if ($this->hasNext()) {
            $nextStep = $this->get($this->currentIndex + 1, false);

            return $nextStep::$slug;
        }

        return null;
    }

    /**
     * @param string $slug
     * @return mixed
     * @throws StepNotFoundException
     */
    public function getBySlug($slug = '')
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

    /**
     * @return Step
     */
    public function first()
    {
        return $this->steps[0];
    }

    /**
     * @param int $moveSteps
     * @return Step
     */
    public function firstOrLastProcessed($moveSteps = 0)
    {
        $lastProcessed = $this->lastProcessed() ?: 0;
        $lastProcessed += $moveSteps;
        $this->currentIndex = $lastProcessed;

        return $this->steps[$lastProcessed];
    }

    /**
     * @return bool|null
     */
    public function lastProcessed()
    {
        $data = $this->data();
        if ($data) {
            $lastProcessed = isset($data['lastProcessed']) ? $data['lastProcessed'] : false;

            return $lastProcessed;
        }

        return null;
    }

    /**
     * @param null $data
     * @return void|array
     */
    public function data($data = null)
    {
        if (is_array($data)) {
            $data['lastProcessed'] = $this->currentIndex;

            session([$this->sessionKeyName => $data]);
        }

        return session($this->sessionKeyName, $default = []);
    }

    /**
     * @param $key
     * @return bool
     */
    public function dataHas($key)
    {
        $data = $this->data();

        return isset($data[$key]);
    }

    /**
     * @param $key
     * @return mixed
     */
    public function dataGet($key)
    {
        $data = $this->data();

        return $data[$key];
    }

    /**
     * @param $step
     * @param $key
     * @return mixed
     */
    public function dataStep($step, $key)
    {
        $data = $this->data();

        return $data[$step::$slug][$key];
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->steps;
    }

}