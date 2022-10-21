<?php

namespace Monet\Framework\Auth\Http\Livewire;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Monet\Framework\Auth\Models\User;
use Monet\Framework\Form\FormBuilder;
use Monet\Framework\Transformer\Facades\Transformer;

class PasswordReset extends Component implements HasForms
{
    use InteractsWithForms;
    use WithRateLimiting;

    public $email = '';

    protected $queryString = [
        'email' => ['except' => ''],
    ];

    public function mount(): void
    {
        if (auth()->check()) {
            redirect()->intended();
        }

        $this->form->fill([
            'email' => $this->email,
            'token' => request()->token,
        ]);
    }

    public function render(): View
    {
        return view('monet::livewire.auth.password-reset');
    }

    public function submit()
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            throw ValidationException::withMessages([
                'email' => __('filament::register.messages.throttled', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]),
            ]);
        }

        $data = $this->form->getState();

        $status = Password::reset(
            [
                ...$data,
                'email' => $this->email,
                'token' => $this->token,
            ],
            function ($user) use ($data) {
                $user->forceFill([
                    User::getAuthPasswordName() => Hash::make($data[User::getAuthPasswordName()]),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new \Illuminate\Auth\Events\PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            Notification::make()
                ->success()
                ->title(__($status))
                ->body('Please login with your new password')
                ->send();

            return redirect()->route('login');
        }

        throw ValidationException::withMessages([
            'email' => __($status),
        ]);
    }

    protected function getFormSchema(): array
    {
        return Transformer::transform(
            'password-reset.form',
            FormBuilder::make()
                ->schema([
                    Placeholder::make('email')
                        ->label('Email address')
                        ->content($this->email),
                    TextInput::make(User::getAuthPasswordName())
                        ->label('Password')
                        ->password()
                        ->required()
                        ->rules('confirmed'),
                    TextInput::make(User::getAuthPasswordName() . '_confirmation')
                        ->label('Confirm password')
                        ->password()
                        ->required(),
                ])
        )->build();
    }
}
