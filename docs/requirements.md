---
title: Requirements
sort: 2
---

## General Requirements

- PHP **8.1** or greater
- Laravel **9.12** or greater

## Browser Requirements

Most modern browsers support both platform and cross-platform WebAuthn. You can check which browsers support at [caniuse](https://caniuse.com/webauthn). In addition,
your application must meet these requirements:

- A proper domain name (localhost and 127.0.0.1 are rejected by our `webauthn.js` script)
- Site must run on an SSL/TLS certificate (self-signed is okay)

## Version Matrix
| Laravel | Minimum Version | Maximum Version |
| --- | --- | --- |
| 9.12 | 1.0.0 | |
