<?php

declare(strict_types=1);

namespace Rawilk\Webauthn\Facades;

use Illuminate\Contracts\Auth\Authenticatable as User;
use Illuminate\Support\Facades\Facade;

/**
 * @see \Rawilk\Webauthn\Services\Webauthn
 *
 * @method static bool canRegister(User $user)
 * @method static bool enabledFor(User $user)
 * @method static bool hasKey(User $user)
 * @method static int keyCountFor(User $user)
 * @method static \Illuminate\Support\Collection keysFor(User $user)
 * @method static \Webauthn\PublicKeyCredentialOptions prepareAttestation(User $user, ?string $attachmentType = null)
 * @method static \Webauthn\PublicKeyCredentialSource validateAttestation(User $user, array $credentials)
 * @method static \Webauthn\PublicKeyCredentialRequestOptions prepareAssertion(User $user)
 * @method static \Rawilk\Webauthn\Contracts\WebauthnKey registerAttestation(User $user, array $credentials, string $keyName)
 * @method static bool|\Webauthn\PublicKeyCredentialSource validateAssertion(User $user, array $credentials)
 * @method static string username()
 * @method static bool webauthnIsEnabled()
 */
class Webauthn extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Rawilk\Webauthn\Services\Webauthn::class;
    }
}
