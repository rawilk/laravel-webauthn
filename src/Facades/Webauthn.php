<?php

namespace Rawilk\Webauthn\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Rawilk\Webauthn\Webauthn
 */
class Webauthn extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'laravel-webauthn';
    }
}
