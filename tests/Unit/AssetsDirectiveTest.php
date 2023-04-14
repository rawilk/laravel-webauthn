<?php

declare(strict_types=1);

use Rawilk\Webauthn\Support\WebauthnAssets;

beforeEach(function () {
    $this->assets = new WebauthnAssets;
});

it('outputs the script source', function () {
    $this->assertStringContainsString(
        '<script src="/webauthn/assets/webauthn.js?',
        $this->assets->javaScript(),
    );
});

it('outputs a comment when app is in debug mode', function () {
    config()->set('app.debug', true);

    $this->assertStringContainsString(
        '<!-- WebAuthn Scripts -->',
        $this->assets->javaScript(),
    );
});

it('does not output a comment when not in debug mode', function () {
    config()->set('app.debug', false);

    $this->assertStringNotContainsString(
        '<!-- WebAuthn Scripts -->',
        $this->assets->javaScript(),
    );
});

it('can use a custom asset url', function () {
    config()->set('webauthn.asset_url', 'https://example.com');

    $this->assertStringContainsString(
        '<script src="https://example.com/webauthn/assets/webauthn.js?',
        $this->assets->javaScript(),
    );
});

it('accepts an asset url as an argument', function () {
    $this->assertStringContainsString(
        '<script src="https://example.com/webauthn/assets/webauthn.js?',
        $this->assets->javaScript(['asset_url' => 'https://example.com']),
    );
});

it('can output a nonce on the script tag', function () {
    $nonce = Str::random(32);

    $this->assertStringContainsString(
        "nonce=\"{$nonce}\"",
        $this->assets->javaScript(['nonce' => $nonce]),
    );
});
