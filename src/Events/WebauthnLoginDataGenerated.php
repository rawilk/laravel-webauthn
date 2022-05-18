<?php

declare(strict_types=1);

namespace Rawilk\Webauthn\Events;

use Illuminate\Contracts\Auth\Authenticatable as User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Webauthn\PublicKeyCredentialRequestOptions;

class WebauthnLoginDataGenerated
{
    use SerializesModels, Dispatchable;

    public function __construct(public User $user, public PublicKeyCredentialRequestOptions $publicKey)
    {
    }
}
