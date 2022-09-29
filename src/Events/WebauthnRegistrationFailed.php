<?php

declare(strict_types=1);

namespace Rawilk\Webauthn\Events;

use Exception;
use Illuminate\Contracts\Auth\Authenticatable as User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WebauthnRegistrationFailed
{
    use SerializesModels;
    use Dispatchable;

    public function __construct(public User $user, public Exception $exception)
    {
    }
}
