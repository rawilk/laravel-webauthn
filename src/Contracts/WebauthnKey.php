<?php

namespace Rawilk\Webauthn\Contracts;

use Illuminate\Contracts\Auth\Authenticatable as User;
use Webauthn\PublicKeyCredentialSource;

interface WebauthnKey
{
    public function getPublicKeyCredentialSourceAttribute(): PublicKeyCredentialSource;

    public function setPublicKeyCredentialSourceAttribute(PublicKeyCredentialSource $source): void;

    public static function fromPublicKeyCredentialSource(PublicKeyCredentialSource $publicKeyCredentialSource, User $user, string $keyName, ?string $attachmentType = null): self;

    /**
     * Return the date the WebAuthn key was created wrapped in a <time>
     * HTML tag.
     *
     * @param string $timezone
     * @return string
     */
    public function createdAtHtml(string $timezone = 'UTC'): string;

    /**
     * Return the date the WebAuthn key was last used wrapped in
     * a <time> HTML tag.
     *
     * @param string $timezone
     * @return string
     */
    public function lastUsedAtHtml(string $timezone = 'UTC'): string;
}
