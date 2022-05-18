<?php

declare(strict_types=1);

namespace Rawilk\Webauthn\Exceptions;

use RuntimeException;

class ResponseMismatchException extends RuntimeException
{
    public static function mismatched(): self
    {
        return new static('Not an authenticator attestation response.');
    }

    public static function assertionMismatched(): self
    {
        return new static('Not an authenticator assertion response.');
    }
}
