<?php

declare(strict_types=1);

namespace Rawilk\Webauthn\Http\Controllers;

use Illuminate\Support\Facades\File;
use Rawilk\Webauthn\Http\Controllers\Concerns\CanPretendToBeAFile;

final class AssetsController
{
    use CanPretendToBeAFile;

    public function source(string $asset)
    {
        $path = __DIR__ . "/../../../dist/assets/{$asset}";

        if (! File::exists($path)) {
            return '';
        }

        return $this->pretendResponseIsFile(__DIR__ . "/../../../dist/assets/{$asset}");
    }
}
