<?php

namespace App\Support;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

final class DefaultAccountPassword
{
    public static function value(): string
    {
        $password = (string) config('onboarding.default_password');
        $validator = Validator::make(
            ['password' => $password],
            ['password' => ['required', Password::min(12)->mixedCase()->numbers()]],
        );

        if ($validator->fails()) {
            throw ValidationException::withMessages([
                'password' => 'The onboarding default password must contain at least 12 characters, uppercase and lowercase letters, and a number.',
            ]);
        }

        return $password;
    }
}
