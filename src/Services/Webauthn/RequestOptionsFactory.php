<?php

declare(strict_types=1);

namespace Rawilk\Webauthn\Services\Webauthn;

use Illuminate\Contracts\Auth\Authenticatable as User;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Http\Request;
use Rawilk\Webauthn\Enums\Userless;
use Rawilk\Webauthn\Enums\UserVerification;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialSourceRepository;

class RequestOptionsFactory extends OptionsFactory
{
    /**
     * User verification preference.
     */
    protected ?string $userVerification;

    public function __construct(
        protected Request $request,
        protected Cache $cache,
        Config $config,
        PublicKeyCredentialSourceRepository $repository,
        protected PublicKeyCredentialRpEntity $publicKeyCredentialRpEntity,
    ) {
        parent::__construct($request, $cache, $config, $repository);

        $this->userVerification = $this->getUserVerification($config);
    }

    public function __invoke(User $user): PublicKeyCredentialRequestOptions
    {
        $publicKey = (new PublicKeyCredentialRequestOptions($this->getChallenge()))
            ->setTimeout($this->timeout)
            ->allowCredentials(...$this->getAllowedCredentials($user))
            ->setRpId($this->getRpId())
            ->setUserVerification($this->userVerification);

        $this->cache->put($this->cacheKey($user), $publicKey, $this->timeout);

        return $publicKey;
    }

    /**
     * Get the user verification preference.
     */
    private function getUserVerification(Config $config): ?string
    {
        return Userless::requiresUserPresence($config->get('webauthn.userless'))
            ? UserVerification::REQUIRED->value
            : $config->get('webauthn.user_verification', UserVerification::PREFERRED->value);
    }

    private function getAllowedCredentials(User $user): array
    {
        return $this->repository->getRegisteredKeys($user);
    }

    private function getRpId(): ?string
    {
        return $this->publicKeyCredentialRpEntity->getId();
    }
}
