<?php

declare(strict_types=1);

namespace Rawilk\Webauthn\Actions;

use Illuminate\Contracts\Auth\Authenticatable as User;
use Rawilk\Webauthn\Facades\Webauthn;
use Webauthn\PublicKeyCredentialOptions;

class PrepareKeyCreationData
{
    public function __invoke(User $user, ?string $attachmentType = null): PublicKeyCredentialOptions
    {
        return Webauthn::prepareAttestation($user, $attachmentType);
    }
}
