# laravel-webauthn

[![Latest Version on Packagist](https://img.shields.io/packagist/v/rawilk/laravel-webauthn.svg?style=flat-square)](https://packagist.org/packages/rawilk/laravel-webauthn)
![Tests](https://github.com/rawilk/laravel-webauthn/workflows/Tests/badge.svg?style=flat-square)
[![Total Downloads](https://img.shields.io/packagist/dt/rawilk/laravel-webauthn.svg?style=flat-square)](https://packagist.org/packages/rawilk/laravel-webauthn)
[![PHP from Packagist](https://img.shields.io/packagist/php-v/rawilk/laravel-webauthn?style=flat-square)](https://packagist.org/packages/rawilk/laravel-webauthn)
[![License](https://img.shields.io/github/license/rawilk/laravel-webauthn?style=flat-square)](https://github.com/rawilk/laravel-webauthn/blob/main/LICENSE.md)

![Social image](https://banners.beyondco.de/laravel-webauthn.png?theme=light&packageManager=composer+require&packageName=rawilk%2Flaravel-webauthn&pattern=randomShapes&style=style_1&description=Add+WebAuthn+functionality+to+Laravel.&md=1&showWatermark=0&fontSize=100px&images=key)

Add the ability to add a hardware based two-factor authentication via a security key, fingerprint or biometric data. Using WebAuthn as a second factor of authentication can help your users better secure their accounts on your application. For more info on WebAuthn, please check out this [guide](https://webauthn.guide/).

## Documentation
For more documentation, please visit the [docs](https://randallwilk.dev/docs/laravel-webauthn).

## Installation

You can install the package via composer:

```bash
composer require rawilk/laravel-webauthn
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="webauthn-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="webauthn-config"
```

You can view the default configuration here: https://github.com/rawilk/laravel-webauthn/blob/main/config/laravel-webauthn.php

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security

Please review [my security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Credits

This package is heavily inspired from Larapass and asbiin/laravel-webauthn.

-   [Randall Wilk](https://github.com/rawilk)
-   [All Contributors](../../contributors)

## Alternatives

This package aims to provide only the bare necessities required to utilize WebAuthn in your application, which provides the freedom to incorporate it into your project based on your own needs and desires. If you're looking for a more complete solution, consider one of these alternatives:

-   [Larapass](https://github.com/DarkGhostHunter/Larapass)
-   [asbiin/laravel-webauthn](https://github.com/asbiin/laravel-webauthn)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
