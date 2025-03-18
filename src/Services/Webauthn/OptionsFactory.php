<?php

declare(strict_types=1);

namespace Rawilk\Webauthn\Services\Webauthn;

use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Http\Request;
use Webauthn\PublicKeyCredentialSourceRepository;

use function random_bytes;

abstract class OptionsFactory extends CredentialValidator
{
    protected CredentialRepository $repository;

    protected int $challengeLength;

    protected int $timeout;

    public function __construct(
        protected Request $request,
        protected Cache $cache,
        Config $config,
        PublicKeyCredentialSourceRepository $repository,
    ) {
        parent::__construct($request, $cache);

        if ($repository instanceof CredentialRepository) {
            $this->repository = $repository;
        }

        $this->challengeLength = (int) $config->get('webauthn.challenge_length', 32);
        $this->timeout = (int) $config->get('webauthn.timeout', 60000);
    }

    protected function getChallenge(): string
    {
        return random_bytes($this->challengeLength);
    }
}
