<?php

namespace Monet\Framework\Auth\Http\Controller;

use Filament\Notifications\Notification;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

class EmailVerificationController extends Controller
{
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        $request->fulfill();

        Notification::make()
            ->success()
            ->title('Email verified successfully')
            ->send();

        return redirect()->intended();
    }
}
