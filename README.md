# laravel-webauthn

[![Latest Version on Packagist](https://img.shields.io/packagist/v/rawilk/laravel-webauthn.svg?style=flat-square)](https://packagist.org/packages/rawilk/laravel-webauthn)
![Tests](https://github.com/rawilk/laravel-webauthn/workflows/Tests/badge.svg?style=flat-square)
[![Total Downloads](https://img.shields.io/packagist/dt/rawilk/laravel-webauthn.svg?style=flat-square)](https://packagist.org/packages/rawilk/laravel-webauthn)
[![PHP from Packagist](https://img.shields.io/packagist/php-v/rawilk/laravel-webauthn?style=flat-square)](https://packagist.org/packages/rawilk/laravel-webauthn)
[![License](https://img.shields.io/github/license/rawilk/laravel-webauthn?style=flat-square)](https://github.com/rawilk/laravel-webauthn/blob/main/LICENSE.md)

![Social image](https://banners.beyondco.de/laravel-webauthn.png?theme=light&packageManager=composer+require&packageName=rawilk%2Flaravel-webauthn&pattern=randomShapes&style=style_1&description=Add+WebAuthn+functionality+to+Laravel.&md=1&showWatermark=0&fontSize=100px&images=key)


This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require rawilk/laravel-webauthn
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="laravel-webauthn-migrations"
php artisan migrate
```

You can publish the config file with:
```bash
php artisan vendor:publish --tag="laravel-webauthn-config"
```

You can view the default configuration here: https://github.com/rawilk/laravel-webauthn/blob/main/config/laravel-webauthn.php

## Usage

``` php
$laravel-webauthn = new Rawilk\Webauthn;
echo $laravel-webauthn->echoPhrase('Hello, Rawilk!');
```

## Testing

``` bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security

Please review [my security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Credits

- [Randall Wilk](https://github.com/rawilk)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
