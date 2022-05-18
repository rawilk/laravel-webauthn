<?php

declare(strict_types=1);

namespace Rawilk\Webauthn\Exceptions;

use Illuminate\Validation\ValidationException;

class WebauthnRegisterException extends ValidationException
{
    public static function registerNotAllowed(string $username): self
    {
        return static::withMessages([
            $username => __('webauthn::alerts.register_not_allowed'),
        ]);
    }

    public static function keyValidationError(string $username): self
    {
        return static::withMessages([
            $username => __('webauthn::alerts.key_validation_error'),
        ]);
    }
}
