<?php

declare(strict_types=1);

namespace Smajti1\LaravelWizard\Test\Step;

use Illuminate\Http\Request;
use Smajti1\Laravel\Step;

class ThirdDumpStep extends Step
{
    public static $label = 'Third step label';
    public static $slug = 'third-step';
    public static $view = '';

    public function process(Request $request)
    {
    }
}