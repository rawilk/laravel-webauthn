---
title: Introduction
sort: 1
---

Add the ability for your users to use a hardware based two-factor authentication for their account via a security key, fingerprint, or biometric data. To accomplish this, this package
utilizes [WebAuthn](https://webauthn.guide/) on both client and server side. You may want to familiarize yourself with WebAuthn before using this package.

As a note, this package only provides the code necessary for registering and asserting credentials. You will need to provide a UI for your users to register security keys, and you will need to incorporate the logic for verifying keys against your users into your authentication workflows. There are alternative's to this package that provide this kind of functionality out-of-the-box, which you can find below.

## Credits

Since the `v1.0.1` patch, a considerable amount of the JavaScript portion of this package has been sourced from the [SimpleWebAuthn](https://github.com/MasterKale/SimpleWebAuthn) package made by [Matthew Miller](https://github.com/MasterKale). His copyright has been added to the license file, and copyright notices have been placed in JS files where the code is extremely close to what his is.

## Alternatives

-   [Larapass](https://github.com/DarkGhostHunter/Larapass)
-   [asbiin/laravel-webauthn](https://github.com/asbiin/laravel-webauthn)
