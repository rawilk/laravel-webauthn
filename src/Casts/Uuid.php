<?php

declare(strict_types=1);

namespace Rawilk\Webauthn\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Symfony\Component\Uid\AbstractUid;
use Symfony\Component\Uid\Uuid as UuidConvert;

class Uuid implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): ?AbstractUid
    {
        if ($value !== null && UuidConvert::isValid($value)) {
            return UuidConvert::fromString($value);
        }

        return null;
    }

    public function set($model, string $key, $value, array $attributes): ?string
    {
        return (string) $value;
    }
}
