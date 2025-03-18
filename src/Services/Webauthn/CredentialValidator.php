<?php

declare(strict_types=1);

namespace Rawilk\Webauthn\Services\Webauthn;

use Illuminate\Contracts\Auth\Authenticatable as User;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Http\Request;

abstract class CredentialValidator
{
    /**
     * PublicKey Request session name.
     *
     * @var string
     */
    public const CACHE_PUBLIC_KEY_REQUEST = 'webauthn.publicKeyRequest';

    public function __construct(
        protected Request $request,
        protected Cache $cache,
    ) {}

    protected function cacheKey(User $user, ?string $attachmentType = null): string
    {
        return implode(
            '|',
            array_filter([
                self::CACHE_PUBLIC_KEY_REQUEST,
                $user::class . ':' . $user->getAuthIdentifier(),
                sha1($this->request->getHost() . '|' . $this->request->ip()),
                $attachmentType,
            ]),
        );
    }
}
