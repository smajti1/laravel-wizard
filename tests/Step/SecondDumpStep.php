<?php

declare(strict_types=1);

namespace Smajti1\LaravelWizard\Test\Step;

use Illuminate\Http\Request;
use Smajti1\Laravel\Step;

class SecondDumpStep extends Step
{
    public static $label = 'Second step label';
    public static $slug = 'second-step';
    public static $view = '';

    public function process(Request $request)
    {
    }
}