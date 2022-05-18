<?php

declare(strict_types=1);

namespace Rawilk\Webauthn\Services\Webauthn;

use Illuminate\Contracts\Auth\Authenticatable as User;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Rawilk\Webauthn\Exceptions\ResponseMismatchException;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialLoader;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialSource;

class CredentialAssertionValidator extends CredentialValidator
{
    public function __construct(
        protected Request $request,
        protected Cache $cache,
        protected ServerRequestInterface $serverRequest,
        protected PublicKeyCredentialLoader $loader,
        protected AuthenticatorAssertionResponseValidator $validator,
    ) {
        parent::__construct($request, $cache);
    }

    public function __invoke(User $user, array $data): bool|PublicKeyCredentialSource
    {
        if (! $publicKeyRequestOptions = $this->pullPublicKey($user)) {
            return false;
        }

        try {
            $publicKeyCredential = $this->loader->loadArray($data);

            return $this->validator->check(
                $publicKeyCredential->getRawId(),
                $this->getResponse($publicKeyCredential),
                $publicKeyRequestOptions,
                $this->serverRequest,
                (string) $user->getAuthIdentifier(),
            );
        } catch (InvalidArgumentException) {
            return false;
        }
    }

    protected function pullPublicKey(User $user): ?PublicKeyCredentialRequestOptions
    {
        $publicKeyCredentialRequestOptions = $this->cache->pull($this->cacheKey($user));

        if (! $publicKeyCredentialRequestOptions instanceof PublicKeyCredentialRequestOptions) {
            return null;
        }

        return $publicKeyCredentialRequestOptions;
    }

    protected function getResponse(PublicKeyCredential $publicKeyCredential): AuthenticatorAssertionResponse
    {
        $response = $publicKeyCredential->getResponse();

        if (! $response instanceof AuthenticatorAssertionResponse) {
            throw ResponseMismatchException::assertionMismatched();
        }

        return $response;
    }
}
