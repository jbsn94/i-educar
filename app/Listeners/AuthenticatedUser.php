<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Authenticated;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthenticatedUser
{
    /**
     * Handle the event.
     *
     * @param Authenticated $event
     *
     * @return void
     */
    public function handle(Authenticated $event)
    {
        if ($event->user->isInactive()) {
            Auth::logout();

            throw ValidationException::withMessages([
                $event->user->login => __('auth.inactive')
            ]);
        }
    }
}
