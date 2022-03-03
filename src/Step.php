<?php

namespace Smajti1\Laravel;

use Illuminate\Http\Request;

abstract class Step
{

    /**
     * @deprecated since 1.1.0 $label will be no more static
	 * @var string
     */
    public static $label;

    /**
     * @deprecated from 1.1.0 $slug will be no more static
	 * @var string
     */
    public static $slug;

    /**
     * @deprecated from 1.1.0 $view will be no more static
	 * @var string
     */
    public static $view;
	/** @var int */
    public $number;
	/** @var int|string */
    public $key;
	/** @var int */
    public $index;
	/** @var Wizard */
    protected $wizard;

	/**
	 * @param int|string $key
	 */
    public function __construct(int $number, $key, int $index, Wizard $wizard)
    {
        $this->number = $number;
        $this->key = $key;
        $this->index = $index;
        $this->wizard = $wizard;
    }

	/**
	 * @return void
	 */
    abstract public function process(Request $request);

	/**
	 * @return array{string, string}|array{}
	 */
    public function rules(Request $request = null): array
    {
        return [];
    }

	/**
	 * @param array<mixed> $additionalData
	 * @return void
	 */
    public function saveProgress(Request $request, array $additionalData = [])
    {
        $wizardData = $this->wizard->data();
        $wizardData[$this::$slug] = $request->except('step', '_token');
        $wizardData = array_merge($wizardData, $additionalData);

        $this->wizard->data($wizardData);
    }

    public function clearData(): void
    {
        $data = $this->wizard->data();
        unset($data[$this::$slug]);
        $this->wizard->data($data);
    }
}
