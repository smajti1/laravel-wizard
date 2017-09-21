# Laravel-wizard

simple laravel step-by-step wizard

## Required

    php ^7.0
    laravel ^5.5

## Install

    $ composer require smajti1/laravel-wizard

## Example/How

1. add routes
    
    ```php
    Route::get('wizard/user/{step?}', 'UserWizardController@wizard')->name('wizard.user');
    Route::post('wizard/user/{step}', 'UserWizardController@wizardPost')->name('wizard.user.post');
    ```

2. create steps

    add autoload field in composer.json file:

        ...
        "autoload": {
           "psr-4": {
                ...
                "App\\Wizard\\Steps\\": "app/Steps"
            },
        ...
    
    regenerate autoloader
    
        $ composer dump-autoload
    
    create step app/Steps/SetUserNameStep.php
    
    ```php
    namespace App\Wizard\Steps;
    
    class SetUserNameStep extends \Smajti1\Laravel\Step
    {
    
        public static $label = 'Set user name';
        public static $slug = 'set-user-name';
        public static $view = 'wizard.user.steps._set_user_name';
    
        public function process(\Illuminate\Http\Request $request)
        {
            // for example, create user
            ...
            // next if you want save one step progress to session use
            $this->saveProgress($request);
        }
    
        public function rules(\Illuminate\Http\Request $request = null): array
        {
            return [
                'username' => 'required|min:4|max:255',
            ];
        }
    }
    ```
    
3. create controller

    ```php
    public $steps = [
        'set-username-key' => SetUserNameStep::class,
        SetPhoneStep::class,
        ...
    ];

    protected $wizard;

    public function __construct()
    {
        $this->wizard = new Wizard($this->steps, $sessionKeyName = 'user');
    }

    public function wizard($step = null)
    {
        try {
            if (is_null($step)) {
                $step = $this->wizard->firstOrLastProcessed();
            } else {
                $step = $this->wizard->getBySlug($step);
            }
        } catch (StepNotFoundException $e) {
            abort(404);
        }

        return view('wizard.user.base', compact('step'));
    }

    public function wizardPost(Request $request, $step = null)
    {
        try {
            $step = $this->wizard->getBySlug($step);
        } catch (StepNotFoundException $e) {
            abort(404);
        }

        $this->validate($request, $step->rules($request));
        $step->process($request);

        return redirect()->route('wizard.user', [$this->wizard->nextSlug()]);
    }
    ```

4. add base view
$wizard variable is now automatic sheared with view
    ```php
    <ol>
        @foreach($wizard->all() as $key => $_step)
            <li>
                @if($step->index == $_step->index)
                    <strong>{{ $_step::$label }}</strong>
                @elseif($step->index > $_step->index)
                    <a href="{{ route('wizard.user', [$_step::$slug]) }}">{{ $_step::$label }}</a>
                @else
                    {{ $_step::$label }}
                @endif
            </li>
        @endforeach
    </ol>
    <form action="{{ route('wizard.user.post', [$step::$slug]) }}" method="POST">
        {{ csrf_field() }}
     
        @include($step::$view, compact('step', 'errors'))
    
        @if ($wizard->hasPrev())
            <a href="{{ route('wizard.user', ['step' => $wizard->prevSlug()]) }}">Back</a>
        @else
            <a href="#">Back</a>
        @endif
    
        <span>Step {{ $step->number }}/{{ $wizard->limit() }}</span>
    
        @if ($wizard->hasNext())
            <button type="submit">Next</button>
        @else
            <button type="submit">Done</button>
        @endif
    </form>
    ```
## License

Laravel wizard is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT)