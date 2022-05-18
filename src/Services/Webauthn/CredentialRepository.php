<?php

declare(strict_types=1);

namespace Rawilk\Webauthn\Services\Webauthn;

use Illuminate\Contracts\Auth\Authenticatable as User;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Rawilk\Webauthn\Contracts\WebauthnKey;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\PublicKeyCredentialSourceRepository;
use Webauthn\PublicKeyCredentialUserEntity;

class CredentialRepository implements PublicKeyCredentialSourceRepository
{
    public function __construct(protected AuthFactory $auth)
    {
    }

    public function findOneByCredentialId(string $publicKeyCredentialId): ?PublicKeyCredentialSource
    {
        try {
            $webauthnKey = $this->model($publicKeyCredentialId);

            return $webauthnKey->public_key_credential_source;
        } catch (ModelNotFoundException) {}

        return null;
    }

    public function findAllForUserEntity(PublicKeyCredentialUserEntity $publicKeyCredentialUserEntity): array
    {
        return $this->getAllRegisteredKeys($publicKeyCredentialUserEntity->getId())
            ->toArray();
    }

    public function saveCredentialSource(PublicKeyCredentialSource $publicKeyCredentialSource): void
    {
        $webauthnKey = $this->model($publicKeyCredentialSource->getPublicKeyCredentialId());

        $webauthnKey->public_key_credential_source = $publicKeyCredentialSource;
        $webauthnKey->last_used_at = now();
        $webauthnKey->save();
    }

    /**
     * List all PublicKeyCredentialSource associated with a user.
     *
     * @param $userId
     * @return \Illuminate\Support\Collection<int, \Webauthn\PublicKeyCredentialSource>
     */
    protected function getAllRegisteredKeys($userId): Collection
    {
        return app(WebauthnKey::class)::query()
            ->where('user_id', $userId)
            ->get()
            ->map
            ->public_key_credential_source;
    }

    /**
     * List all registered PublicKeyCredentialDescriptor associated with a user.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @return array<int, \Webauthn\PublicKeyCredentialDescriptor>
     */
    public function getRegisteredKeys(User $user): array
    {
        return $this->getAllRegisteredKeys($user->getAuthIdentifier())
            ->map
            ->getPublicKeyCredentialDescriptor()
            ->toArray();
    }

    private function model(string $credentialId)
    {
        return app(WebauthnKey::class)::query()
            ->when($this->guard()->check(), fn ($query) => $query->where('user_id', $this->guard()->id()))
            ->where(function ($query) use ($credentialId) {
                $query->where('credential_id', Base64UrlSafe::encode($credentialId))
                    ->orWhere('credential_id', Base64UrlSafe::encodeUnpadded($credentialId));
            })
            ->firstOrFail();
    }

    private function guard(): \Illuminate\Contracts\Auth\Guard|\Illuminate\Contracts\Auth\StatefulGuard
    {
        return $this->auth->guard();
    }
}
