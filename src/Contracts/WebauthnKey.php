<?php

namespace Rawilk\Webauthn\Contracts;

use Illuminate\Contracts\Auth\Authenticatable as User;
use Webauthn\PublicKeyCredentialSource;

interface WebauthnKey
{
    public function getPublicKeyCredentialSourceAttribute(): PublicKeyCredentialSource;

    public function setPublicKeyCredentialSourceAttribute(PublicKeyCredentialSource $source): void;

    public static function fromPublicKeyCredentialSource(PublicKeyCredentialSource $publicKeyCredentialSource, User $user, string $keyName, ?string $attachmentType = null): self;
}
