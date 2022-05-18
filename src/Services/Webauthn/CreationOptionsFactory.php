<?php

declare(strict_types=1);

namespace Rawilk\Webauthn\Services\Webauthn;

use Cose\Algorithm\Manager as CoseAlgorithmManager;
use Illuminate\Contracts\Auth\Authenticatable as User;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Http\Request;
use Rawilk\Webauthn\Enums\AttestationConveyancePreference;
use Rawilk\Webauthn\Facades\Webauthn;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\PublicKeyCredentialParameters;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialSourceRepository;
use Webauthn\PublicKeyCredentialUserEntity;

final class CreationOptionsFactory extends OptionsFactory
{
    private string $attestationConveyance;

    public function __construct(
        protected Request $request,
        protected Cache $cache,
        Config $config,
        PublicKeyCredentialSourceRepository $repository,
        private readonly PublicKeyCredentialRpEntity $publicKeyCredentialRpEntity,
        private readonly AuthenticatorSelectionCriteria $authenticatorSelectionCriteria,
        private readonly CoseAlgorithmManager $algorithmManager,
    ) {
        parent::__construct($request, $cache, $config, $repository);

        $this->attestationConveyance = $config->get('webauthn.attestation_conveyance', AttestationConveyancePreference::NONE->value);
    }

    public function __invoke(User $user, ?string $attachmentType = null): PublicKeyCredentialCreationOptions
    {
        $publicKey = (new PublicKeyCredentialCreationOptions(
            $this->publicKeyCredentialRpEntity,
            $this->getUserEntity($user),
            $this->getChallenge(),
            $this->createCredentialParameters(),
        ))
            ->setTimeout($this->timeout)
            ->excludeCredentials(...$this->getExcludedCredentials($user))
            ->setAuthenticatorSelection($this->authenticatorSelectionCriteria)
            ->setAttestation($this->attestationConveyance);

        $this->cache->put($this->cacheKey($user, $attachmentType), $publicKey, $this->timeout);

        return $publicKey;
    }

    private function getUserEntity(User $user): PublicKeyCredentialUserEntity
    {
        return new PublicKeyCredentialUserEntity(
            $user->{Webauthn::username()} ?? '',
            (string) $user->getAuthIdentifier(),
            $user->{Webauthn::username()} ?? '',
            null,
        );
    }

    /**
     * @return array<int, \Webauthn\PublicKeyCredentialParameters>
     */
    private function createCredentialParameters(): array
    {
        return collect($this->algorithmManager->list())
            ->map(function ($algorithm): PublicKeyCredentialParameters {
                return new PublicKeyCredentialParameters(
                    PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY,
                    $algorithm,
                );
            })
            ->toArray();
    }

    private function getExcludedCredentials(User $user): array
    {
        return $this->repository->getRegisteredKeys($user);
    }
}
