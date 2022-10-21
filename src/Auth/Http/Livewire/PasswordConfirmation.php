<?php

namespace Monet\Framework\Auth\Http\Livewire;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Facades\Filament;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Monet\Framework\Form\FormBuilder;
use Monet\Framework\Transformer\Facades\Transformer;

class PasswordConfirmation extends Component implements HasForms
{
    use InteractsWithForms;
    use WithRateLimiting;

    public function mount(): void
    {
        if (!auth()->check()) {
            redirect()->intended(route('login'));
        }

        $this->form->fill();
    }

    public function render(): View
    {
        return view('monet::livewire.auth.password-confirmation');
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

        if (!Filament::auth()->validate([
            'email' => auth()->user()->email,
            'password' => $data['password'],
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        session()->put('auth.password_confirmed_at', time());

        return redirect()->intended();
    }

    protected function getFormSchema(): array
    {
        return Transformer::transform(
            'password-confirmation.form',
            FormBuilder::make()
                ->schema([
                    Placeholder::make('introduction')
                        ->view('monet::components.auth.password-confirmation-introduction'),
                    Placeholder::make('email')
                        ->label('Email address')
                        ->content(auth()->user()->email),
                    TextInput::make('password')
                        ->label('Password')
                        ->password(),
                ])
        )->build();
    }
}
