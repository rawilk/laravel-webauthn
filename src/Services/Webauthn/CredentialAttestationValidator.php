<?php

declare(strict_types=1);

namespace Rawilk\Webauthn\Services\Webauthn;

use Illuminate\Contracts\Auth\Authenticatable as User;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Psr\Http\Message\ServerRequestInterface;
use Rawilk\Webauthn\Exceptions\ResponseMismatchException;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialLoader;
use Webauthn\PublicKeyCredentialSource;

class CredentialAttestationValidator extends CredentialValidator
{
    public function __construct(
        protected Request $request,
        protected Cache $cache,
        protected ServerRequestInterface $serverRequest,
        protected PublicKeyCredentialLoader $loader,
        protected AuthenticatorAttestationResponseValidator $validator,
    ) {
        parent::__construct($request, $cache);
    }

    public function __invoke(User $user, array $data): ?PublicKeyCredentialSource
    {
        if (! $publicKeyCredentialCreationOptions = $this->pullPublicKey($user, Arr::get($data, 'attachment_type'))) {
            return null;
        }

        $publicKeyCredential = $this->loader->loadArray($data);

        return $this->validator->check(
            $this->getResponse($publicKeyCredential),
            $publicKeyCredentialCreationOptions,
            $this->serverRequest,
        );
    }

    protected function pullPublicKey(User $user, ?string $attachmentType): ?PublicKeyCredentialCreationOptions
    {
        $publicKeyCredentialCreationOptions = $this->cache->pull($this->cacheKey($user, $attachmentType));

        if (! $publicKeyCredentialCreationOptions instanceof PublicKeyCredentialCreationOptions) {
            return null;
        }

        return $publicKeyCredentialCreationOptions;
    }

    /**
     * Get the authenticator response.
     *
     * @param  \Webauthn\PublicKeyCredential  $publicKeyCredential
     * @return \Webauthn\AuthenticatorAttestationResponse
     */
    protected function getResponse(PublicKeyCredential $publicKeyCredential): AuthenticatorAttestationResponse
    {
        $response = $publicKeyCredential->getResponse();

        if (! $response instanceof AuthenticatorAttestationResponse) {
            throw ResponseMismatchException::mismatched();
        }

        return $response;
    }
}
