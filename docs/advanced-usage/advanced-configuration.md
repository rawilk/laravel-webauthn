---
title: Advanced Configuration
sort: 2
---

## Introduction

The configuration provided out-of-the-box should be enough for most use cases, however your application may have more advanced needs. You may override the config by publishing it, or with some options you may enter a value into your `.env` file.

## Relying Party Information

The _Relying Party_ is just a way to uniquely identify your application in the user device:

- `name`: The name of the application. Defaults to the application name.
- `id`: Optional domain of the application. If null, the device will fill it internally.
- `icon`: Optional image data in base64 (128 bytes maximum) or an image url.

> {tip} Consider using the base domain like `myapp.com` as the `id` to allow the credentials on subdomains like `acme.myapp.com`.

```php
return [
    'relying_party' => [
        'name' => env('WEBAUTHN_NAME', env('APP_NAME')),
        'id' => env('WEBAUTHN_ID'),
        'icon' => env('WEBAUTHN_ICON'),
    ],
];
```

## Key Attachment

By default, the user decides what kind of key to use for registration. If you with to exclusively use a cross-platform authentication (like USB keys, CA Servers or Certificates), set this value to `cross-platform`. To exclusively use internal authenticators (like Touch ID or Windows Hello), set this value to `platform`. The default is a null value (for both).

```php
return [
    'attachment_mode' => env('WEBAUTHN_ATTACHMENT_MODE'),
];
```

> {tip} If you want to separate cross-platform and platform keys in your UI, you can always set this config value at runtime.

If you decide to separate them out, you may also pass the attachment type to the `RegisterNewKeyAction` to instruct the action to tag a key with a specific platform. This will help you query for your keys by platform later.

```php
app(\Rawilk\Webauthn\Actions\RegisterNewKeyAction::class)(
    auth()->user(),
    array_merge(\Illuminate\Support\Arr::only($request->all(), ['id', 'rawId', 'response', 'type']), ['attachment_type' => 'platform']),
    $request->keyName,
)
```

## Attestation Conveyance

Attestation conveyance represents if the device should be verified by you or not. While most of the time this is not needed, you can change this to `indirect` (you must verify it comes from a trustful source) or `direct` (the device includes validation data).

```php
return [
    'attestation_conveyance' => env('WEBAUTHN_ATTESTATION_CONVEYANCE', \Rawilk\Webauthn\Enums\AttestationConveyancePreference::NONE->value),
];
```

See: https://www.w3.org/TR/webauthn/#enum-attestation-convey

## Login Verification

By default, most authenticators will require the user to verify their identity when logging in (usually a PIN or password on the device). You can override this and set to as `required` if you always want the device to require this (when supported).

You may also set this to `discouraged` to only check for user presence (Like a "continue" button), which may make the login faster but also slightly less secure.

The default for this is `preferred`.

> {note} When setting [userless](#userless-login) to `preferred` or `required`, this will force this value to be `required` automatically.

```php
return [
    'user_verification' => env('WEBAUTHN_USER_VERIFICATION', \Rawilk\Webauthn\Enums\UserVerification::PREFERRED->value),
];
```

## Userless Login

You may activate _userless_ login, also known as one-touch login or typeless login for devices when they're being registered. It's recommended to change this to `preferred` in this case, since not all devices support this feature.

If this is activated (not `null` or `discouraged`), login verification will be mandatory.

> {tip} This doesn't affect the login procedure; only the attestation (registration).

> {note} If you activate this, you will need to create a custom user provider that is capable of locating a user account from a security key during your login process.

```php
return [
    'userless' => env('WEBAUTHN_USERLESS'),
];
```

## Algorithms

This controls how the authenticator (device) will operate to create the public/private keys. These [COSE Algorithms](https://w3c.github.io/webauthn/#typedefdef-cosealgorithmidentifier) are the most compatible ones for in-device and roaming keys, since some must be transmitted on low bandwidth protocols. You will need to publish the config to override this config value.

> {note} In most cases you shouldn't modify this. Only modify if you know what you're doing.

```php
return [
    'public_key_credential_parameters' => [
        (string) \Cose\Algorithms::COSE_ALGORITHM_ES256, // ECDSA with SHA-256
        (string) \Cose\Algorithms::COSE_ALGORITHM_ES512, // ECDSA with SHA-512
        (string) \Cose\Algorithms::COSE_ALGORITHM_RS256, // RSASSA-PKCS1-v1_5 with SHA-256
        (string) \Cose\Algorithms::COSE_ALGORITHM_EdDSA, // EdDSA
        (string) \Cose\Algorithms::COSE_ALGORITHM_ES384, // ECDSA with SHA-384
    ],
];
```
