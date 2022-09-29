<?php

declare(strict_types=1);

namespace Rawilk\Webauthn\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Rawilk\Webauthn\Contracts\WebauthnKey;

class WebauthnKeyWasRegistered
{
    use SerializesModels;
    use Dispatchable;

    public function __construct(public WebauthnKey $webauthnKey)
    {
    }
}
