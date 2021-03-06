<?php

declare(strict_types=1);

namespace Rawilk\Webauthn\Support;

class WebauthnAssets
{
    public function javaScript(array $options = []): string
    {
        $html = config('app.debug') ? ['<!-- WebAuthn Scripts -->'] : [];

        $html[] = $this->javaScriptAssets($options);

        return implode(PHP_EOL, $html);
    }

    private function javaScriptAssets(array $options = []): string
    {
        $appUrl = config('webauthn.asset_url', rtrim($options['asset_url'] ?? '', '/'));

        $manifest = json_decode(file_get_contents(__DIR__ . '/../../dist/mix-manifest.json'), true);
        $versionedFileName = $manifest['/webauthn.js'];

        $fullAssetPath = "{$appUrl}/webauthn{$versionedFileName}";

        return <<<HTML
        <script src="{$fullAssetPath}" data-turbolinks-eval="false"></script>
        HTML;
    }
}
