<?php

namespace Monet\Framework\Auth\Http\Livewire;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Monet\Framework\Auth\Contracts\ShouldVerifyEmail;
use Monet\Framework\Form\FormBuilder;
use Monet\Framework\Transformer\Facades\Transformer;

class EmailVerification extends Component implements HasForms
{
    use InteractsWithForms;
    use WithRateLimiting;

    public function mount(): void
    {
        if (!auth()->check()) {
            redirect()->intended(route('login'));
        }

        $user = auth()->user();
        if (
            $user->hasVerifiedEmail() ||
            ($user instanceof ShouldVerifyEmail && !$user->shouldVerifyEmail())
        ) {
            redirect()->intended();
        }

        $this->form->fill();
    }

    public function render(): View
    {
        return view('monet::livewire.auth.email-verification');
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

        auth()->user()->sendEmailVerificationNotification();

        Notification::make()
            ->success()
            ->title('Email has been successfully sent')
            ->body('Please check your inbox (or your spam)')
            ->send();
    }

    protected function getFormSchema(): array
    {
        return Transformer::transform(
            'email-verification.form',
            FormBuilder::make()
                ->schema([
                    Placeholder::make('introduction')
                        ->view('monet::components.auth.email-verification-introduction'),
                    Placeholder::make('email')
                        ->label('Email address')
                        ->content(auth()->user()->email),
                ])
        )->build();
    }
}
