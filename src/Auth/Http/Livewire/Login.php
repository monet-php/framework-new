<?php

namespace Monet\Framework\Auth\Http\Livewire;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Facades\Filament;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Monet\Framework\Auth\Auth;
use Monet\Framework\Auth\Models\User;
use Monet\Framework\Form\FormBuilder;
use Monet\Framework\Transformer\Facades\Transformer;

class Login extends Component implements HasForms
{
    use InteractsWithForms;
    use WithRateLimiting;

    public function mount(): void
    {
        if (Filament::auth()->check()) {
            redirect()->intended();
        }

        $this->form->fill();
    }

    public function render(): View
    {
        return view('monet::livewire.auth.login');
    }

    public function submit()
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            throw ValidationException::withMessages([
                'email' => __('filament::login.messages.throttled', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]),
            ]);
        }

        $data = $this->form->getState();

        Auth::login($data);

        return redirect('/');
    }

    protected function getFormSchema(): array
    {
        return Transformer::transform(
            'login.form',
            FormBuilder::make()
                ->schema([
                    Placeholder::make('register_link')
                        ->view('monet::components.auth.register-link'),
                    TextInput::make('email')
                        ->label(function () {
                            if (config('monet.auth.allow_username_login')) {
                                return 'Email address or username';
                            }

                            return 'Email address';
                        })
                        ->required()
                        ->autocomplete(),
                    TextInput::make(User::getAuthPasswordName())
                        ->label('Password')
                        ->password()
                        ->required(),
                    Grid::make()
                        ->schema([
                            Checkbox::make('remember')
                                ->label('Remember me'),
                            Placeholder::make('forgot_password')
                                ->view('monet::components.auth.forgot-password-link'),
                        ])
                        ->columns([
                            'default' => 2,
                        ]),
                ])
        )->build();
    }
}
