---
title: Installation & Setup
sort: 3
---

## Installation

laravel-webauthn can be installed via composer:

```bash
composer require rawilk/laravel-webauthn
```

## Configuration

### Publishing the config file

You may publish the config file like this:

```bash
php artisan vendor:publish --tag="webauthn-config"
```

See the default configuration values [here](https://github.com/rawilk/laravel-webauthn/blob/main/config/webauthn.php).

### Configuring the package

No custom configuration is required out-of-the-box, however some common configurations you may want to change include:

- `user_verification`: Basically determines if the user needs to enter a PIN for their security key. Set to `discouraged` to not require that.
- `attachment_mode`: Determines which type of authenticator the user may use. Use `platform` for internal (biometric), and `cross-platform` for roaming (security) keys.

## Migrations

If you plan to use the table and model provided by this package, you will need to publish and run the package's migrations.

```bash
php artisan vendor:publish --tag="webauthn-migrations"
php artisan migrate
```

## Models

### WebauthnKey Model

The package provides a model for representing a WebAuthn key that is registered to a user's account. You may extend our model or use your own model by specifying it in the configuration.

```php
<?php

return [
    ...
    'database' => [
        ...
        'model' => \Rawilk\Webauthn\Models\WebauthnKey::class,    
    ],
],
```

> {note} If you use your own model, it must implement the `\Rawilk\Webauthn\Contracts\WebauthnKey` interface!

## Translations

At various points in the registration or assertion process, exceptions may be thrown by either our client or server side scripts. You may publish and modify the language files with this command:

```bash
php artisan vendor:publish --tag="webauthn-translations"
```
