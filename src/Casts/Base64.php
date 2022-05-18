<?php

declare(strict_types=1);

namespace Rawilk\Webauthn\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Webauthn\Util\Base64 as Base64Webauthn;

class Base64 implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): ?string
    {
        return $value !== null ? Base64Webauthn::decode($value) : null;
    }

    public function set($model, string $key, $value, array $attributes)
    {
        return $value !== null ? Base64UrlSafe::encode($value) : null;
    }
}
