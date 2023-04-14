<?php

declare(strict_types=1);

namespace Rawilk\Webauthn\Support;

use Illuminate\Support\Facades\Vite;

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
        $assetsUrl = config('webauthn.asset_url') ?: rtrim($options['asset_url'] ?? '', '/');
        $nonce = $this->getNonce($options);

        $manifest = json_decode(file_get_contents(__DIR__ . '/../../dist/mix-manifest.json'), true);
        $versionedFileName = ltrim($manifest['/assets/webauthn.js'], '/');

        $fullAssetPath = "{$assetsUrl}/webauthn/{$versionedFileName}";

        return <<<HTML
        <script src="{$fullAssetPath}" data-turbolinks-eval="false" data-turbo-eval="false" {$nonce}></script>
        HTML;
    }

    private function getNonce(array $options): string
    {
        if (isset($options['nonce'])) {
            return "nonce=\"{$options['nonce']}\"";
        }

        // If there is a csp package installed, i.e. spatie/laravel-csp, we'll check for the existence of the helper function.
        if (function_exists('csp_nonce') && $nonce = csp_nonce()) {
            return "nonce=\"{$nonce}\"";
        }

        // Lastly, we'll check for the existence of a csp nonce from Vite.
        if (class_exists(Vite::class) && $nonce = Vite::cspNonce()) {
            return "nonce=\"{$nonce}\"";
        }

        return '';
    }
}
