<?php

declare(strict_types=1);

namespace Rawilk\Webauthn\Enums;

enum UserVerification: string
{
    case REQUIRED = 'required';
    case PREFERRED = 'preferred';
    case DISCOURAGED = 'discouraged';
}
