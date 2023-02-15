<?php

declare(strict_types=1);

namespace Rawilk\Webauthn\Services;

use Illuminate\Contracts\Auth\Authenticatable as User;
use Illuminate\Support\Arr;
use Rawilk\Webauthn\Contracts\WebauthnKey;
use Rawilk\Webauthn\Events\WebauthnKeyWasRegistered;
use Rawilk\Webauthn\Events\WebauthnLoginDataGenerated;
use Rawilk\Webauthn\Events\WebauthnRegisterData;
use Rawilk\Webauthn\Exceptions\WebauthnRegisterException;
use Rawilk\Webauthn\Services\Webauthn\CreationOptionsFactory;
use Rawilk\Webauthn\Services\Webauthn\CredentialAssertionValidator;
use Rawilk\Webauthn\Services\Webauthn\CredentialAttestationValidator;
use Rawilk\Webauthn\Services\Webauthn\RequestOptionsFactory;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialSource;

class Webauthn extends WebauthnRepository
{
    /**
     * Get a publicKey data set to prepare WebAuthn key creation.
     *
     * @param  null|string  $attachmentType For generating a different cache key name for multiple sections of keys in the UI
     */
    public function prepareAttestation(User $user, ?string $attachmentType = null): PublicKeyCredentialCreationOptions
    {
        return tap(app(CreationOptionsFactory::class)($user, $attachmentType), function ($publicKey) use ($user) {
            WebauthnRegisterData::dispatch($user, $publicKey);
        });
    }

    /**
     * Validate a WebAuthn key creation request.
     */
    public function validateAttestation(User $user, array $credentials): ?PublicKeyCredentialSource
    {
        return app(CredentialAttestationValidator::class)($user, $credentials);
    }

    /**
     * Register a new WebAuthn key for a given user.
     */
    public function registerAttestation(User $user, array $credentials, string $keyName): WebauthnKey
    {
        $publicKey = $this->validateAttestation($user, $credentials);

        if (! $publicKey) {
            throw WebauthnRegisterException::keyValidationError($this->username());
        }

        return tap(
            $this->create($user, $keyName, $publicKey, Arr::get($credentials, 'attachment_type')),
            function (WebauthnKey $webauthnKey) {
                WebauthnKeyWasRegistered::dispatch($webauthnKey);
            }
        );
    }

    /**
     * Get publicKey data to prepare a WebAuthn login.
     */
    public function prepareAssertion(User $user): PublicKeyCredentialRequestOptions
    {
        return tap(app(RequestOptionsFactory::class)($user), function (PublicKeyCredentialRequestOptions $publicKey) use ($user) {
            WebauthnLoginDataGenerated::dispatch($user, $publicKey);
        });
    }

    /**
     * Validate a WebAuthn login request.
     */
    public function validateAssertion(User $user, array $credentials): bool|PublicKeyCredentialSource
    {
        return app(CredentialAssertionValidator::class)($user, $credentials);
    }

    public function username(): string
    {
        return config('webauthn.username', 'email');
    }

    /**
     * Check if a given user can register a new key.
     */
    public function canRegister(User $user): bool
    {
        return $this->webauthnIsEnabled();
    }

    /**
     * Check if both WebAuthn is enabled for the application and that
     * the given user has at least one key registered to them.
     */
    public function enabledFor(User $user): bool
    {
        return $this->webauthnIsEnabled() && $this->hasKey($user);
    }

    /**
     * Check if webauthn is configured to be enabled for this application.
     */
    public function webauthnIsEnabled(): bool
    {
        return (bool) config('webauthn.enabled', true);
    }
}
