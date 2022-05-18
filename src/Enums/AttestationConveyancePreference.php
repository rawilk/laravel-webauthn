<?php

declare(strict_types=1);

namespace Rawilk\Webauthn\Enums;

enum AttestationConveyancePreference: string
{
    case NONE = 'none';
    case INDIRECT = 'indirect';
    case DIRECT = 'direct';
    case ENTERPRISE = 'enterprise';
}
