# Changelog

All notable changes to `laravel-webauthn` will be documented in this file

## 1.0.3 - 2022-09-29
### Changed
- Revert build process to use laravel-mix

## 1.0.2 - 2022-09-29
### Fixed
- Fix manifest.json path in `WebauthnAssets.php` helper

## 1.0.1 - 2022-09-29
### Fixed
- Fix decodeNoPadding() doesn't tolerate padding issues caused from front-end scripts - [See web-auth/webauthn-framework #285](https://github.com/web-auth/webauthn-framework/issues/285)

### Changed
- Change build process from laravel-mix to vite

## 1.0.0 - 2022-06-06

-   initial release
