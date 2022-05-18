<?php

declare(strict_types=1);

namespace Rawilk\Webauthn\Enums;

enum Userless: string
{
    case REQUIRED = 'required';
    case PREFERRED = 'preferred';
    case DISCOURAGED = 'discouraged';

    public static function requiresUserPresence(?string $value): bool
    {
        if ($value === null) {
            return false;
        }

        $enum = self::tryFrom(strtolower($value));

        return $enum === self::REQUIRED || $enum === self::PREFERRED;
    }
}
