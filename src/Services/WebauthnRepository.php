<?php

declare(strict_types=1);

namespace Rawilk\Webauthn\Services;

use Illuminate\Contracts\Auth\Authenticatable as User;
use Illuminate\Support\Collection;
use Rawilk\Webauthn\Contracts\WebauthnKey;
use Webauthn\PublicKeyCredentialSource;

abstract class WebauthnRepository
{
    public function create(User $user, string $keyName, PublicKeyCredentialSource $publicKeyCredentialSource, ?string $attachmentType = null): WebauthnKey
    {
        return app(WebauthnKey::class)::fromPublicKeyCredentialSource(
            $publicKeyCredentialSource,
            $user,
            $keyName,
            $attachmentType,
        );
    }

    public function keyCountFor(User $user): int
    {
        return app(WebauthnKey::class)::query()
            ->where('user_id', $user->getAuthIdentifier())
            ->count();
    }

    public function keysFor(User $user): Collection
    {
        return app(WebauthnKey::class)::query()
            ->where('user_id', $user->getAuthIdentifier())
            ->get();
    }

    public function hasKey(User $user): bool
    {
        return $this->keyCountFor($user) > 0;
    }
}
