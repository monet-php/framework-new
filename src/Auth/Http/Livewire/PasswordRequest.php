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
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Monet\Framework\Form\FormBuilder;
use Monet\Framework\Transformer\Facades\Transformer;

class PasswordRequest extends Component implements HasForms
{
    use InteractsWithForms;
    use WithRateLimiting;

    public $email = '';

    public function mount(): void
    {
        if (auth()->check()) {
            redirect()->intended();
        }

        $this->form->fill();
    }

    public function render(): View
    {
        return view('monet::livewire.auth.password-request');
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

        $status = Password::sendResetLink($data);

        if ($status === Password::RESET_LINK_SENT) {
            Notification::make()
                ->success()
                ->title(__($status))
                ->body('Please check your inbox (or your spam)')
                ->send();

            return;
        }

        throw ValidationException::withMessages([
            'email' => __($status),
        ]);
    }

    protected function getFormSchema(): array
    {
        return Transformer::transform(
            'password-request.form',
            FormBuilder::make()
                ->schema([
                    Placeholder::make('register_link')
                        ->view('monet::components.auth.remember-password-link'),
                    Placeholder::make('introduction')
                        ->view('monet::components.auth.password-request-introduction'),
                    TextInput::make('email')
                        ->label('Email address')
                        ->email()
                        ->required()
                        ->autocomplete(),
                ])
        )->build();
    }
}
