<?php

declare(strict_types=1);

namespace Rawilk\Webauthn\Actions;

use Illuminate\Contracts\Auth\Authenticatable as User;
use Rawilk\Webauthn\Facades\Webauthn;
use Webauthn\PublicKeyCredentialRequestOptions;

class PrepareAssertionData
{
    public function __invoke(User $user): PublicKeyCredentialRequestOptions
    {
        return Webauthn::prepareAssertion($user);
    }
}
