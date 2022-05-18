<?php

declare(strict_types=1);

namespace Rawilk\Webauthn\Actions;

use Exception;
use Illuminate\Contracts\Auth\Authenticatable as User;
use Rawilk\Webauthn\Contracts\WebauthnKey;
use Rawilk\Webauthn\Events\WebauthnRegistrationFailed;
use Rawilk\Webauthn\Exceptions\WebauthnRegisterException;
use Rawilk\Webauthn\Facades\Webauthn;

class RegisterNewKeyAction
{
    public function __invoke(User $user, array $data, string $keyName): WebauthnKey
    {
        if (! Webauthn::canRegister($user)) {
            throw WebauthnRegisterException::registerNotAllowed(Webauthn::username());
        }

        return $this->registerKey($user, $data, $keyName);
    }

    protected function registerKey(User $user, array $data, string $keyName): WebauthnKey
    {
        try {
            return Webauthn::registerAttestation($user, $data, $keyName);
        } catch (Exception $e) {
            WebauthnRegistrationFailed::dispatch($user, $e);

            throw WebauthnRegisterException::keyValidationError(Webauthn::username());
        }
    }
}
