<?php

namespace Smajti1\Laravel;

use Illuminate\Http\Request;

abstract class Step
{

    /**
     * @deprecated since 1.1.0 $label will be no more static
     */
    public static $label;

    /**
     * @deprecated from 1.1.0 $slug will be no more static
     */
    public static $slug;

    /**
     * @deprecated from 1.1.0 $view will be no more static
     */
    public static $view;
    public $number;
    public $key;
    public $index;
    protected $wizard;

    public function __construct(int $number, $key, int $index, Wizard $wizard)
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