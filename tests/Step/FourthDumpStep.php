<?php

declare(strict_types=1);

namespace Smajti1\LaravelWizard\Test\Step;

use Illuminate\Http\Request;
use Smajti1\Laravel\Step;

class FourthDumpStep extends Step
{
    public static $label = 'Fourth step label';
    public static $slug = 'fourth-step';
    public static $view = '';

    public function process(Request $request)
    {
    }
}