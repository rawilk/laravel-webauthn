<?php

declare(strict_types=1);

namespace Rawilk\Webauthn\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Webauthn\TrustPath\TrustPathLoader;

class TrustPath implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): ?\Webauthn\TrustPath\TrustPath
    {
        return $value !== null
            ? TrustPathLoader::loadTrustPath(json_decode($value, true))
            : null;
    }

    public function set($model, string $key, $value, array $attributes)
    {
        return json_encode($value);
    }
}
