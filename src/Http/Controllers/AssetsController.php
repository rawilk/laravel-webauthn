<?php

declare(strict_types=1);

namespace Rawilk\Webauthn\Http\Controllers;

use Rawilk\Webauthn\Http\Controllers\Concerns\CanPretendToBeAFile;

final class AssetsController
{
    use CanPretendToBeAFile;

    public function source()
    {
        return $this->pretendResponseIsFile(__DIR__ . '/../../../dist/webauthn.js');
    }

    public function maps()
    {
        return $this->pretendResponseIsFile(__DIR__ . '/../../../dist/webauthn.js.map');
    }
}
