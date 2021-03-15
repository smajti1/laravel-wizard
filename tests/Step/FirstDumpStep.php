<?php

declare(strict_types=1);

namespace Smajti1\LaravelWizard\Test\Step;

use Illuminate\Http\Request;
use Smajti1\Laravel\Step;

class FirstDumpStep extends Step
{
    public static $label = 'First step label';
    public static $slug = 'first-step';
    public static $view = '';

    public function process(Request $request)
    {
    }
}