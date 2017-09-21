<?php

namespace Smajti1\Laravel;

use Illuminate\Http\Request;

abstract class Step
{

    public static $label;
    public static $slug;
    public static $view;
    public $number;
    public $key;
    public $index;
    protected $wizard;

    public function __construct(int $number, string $key, int $index, Wizard $wizard)
    {
        $this->number = $number;
        $this->key = $key;
        $this->index = $index;
        $this->wizard = $wizard;
    }

    abstract public function process(Request $request);

    public function rules(Request $request = null): array
    {
        return [];
    }

    public function saveProgress(Request $request, array $additionalData = [])
    {
        $wizardData = $this->wizard->data();
        $wizardData[$this::$slug] = $request->except('step', '_token');
        $wizardData = array_merge($wizardData, $additionalData);

        $this->wizard->data($wizardData);
    }

}