<?php

declare(strict_types=1);

namespace Rawilk\Webauthn\Http\Controllers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Rawilk\Webauthn\Http\Controllers\Concerns\CanPretendToBeAFile;

final class AssetsController
{
    use CanPretendToBeAFile;

    public function source(string $asset)
    {
        $fileName = Str::before($asset, '?');

        $path = __DIR__ . "/../../../dist/assets/{$fileName}";

        if (! File::exists($path)) {
            return '';
        }

        return $this->pretendResponseIsFile($path);
    }
}
