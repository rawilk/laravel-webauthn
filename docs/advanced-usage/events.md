---
title: Events
sort: 1
---

## Introduction

There are various events that we will dispatch from this package. All of them are in the `\Rawilk\Webauthn\Events` namespace. Your application may listen for these events and act accordingly to them.

## WebauthnKeyWasRegistered

When a new `Webauthn` key is created for a user, we will dispatch this event. It may be helpful to listen for this event and send an email notification to the user when this happens.

Params:
- `\Rawilk\Webauthn\Contracts\WebauthnKey $webauthnKey`

The following events are dispatched by this package:

## WebauthnKeyWasUsed

We dispatch this when a WebauthnKey is used to verify a user's identity during the authentication process. We automatically update the `last_used_at` timestamp on the key, so you won't have to do that yourself.

Params:
- `\Rawilk\Webauthn\Contracts\WebauthnKey $webauthnKey`

## WebauthnLoginDataGenerated

This is dispatched when we generate an assertion (public key) for a given user to be used by our JavaScript.

Params:
- `\Illuminate\Contracts\Auth\Authenticable $user`
- `\Webauthn\PublicKeyCredentialRequestOptions $publicKey`

## WebauthnRegisterData

This is dispatched when we generate an attestation (public key) for a given user to be used by our JavaScript.

Params:
- `\Illuminate\Contracts\Auth\Authenticable $user`
- `\Webauthn\PublicKeyCredentialCreationOptions $publicKey`

## WebauthnRegistrationFailed

We dispatch this when a WebauthnKey fails to be generated for a given user.

Params:
- `\Illuminate\Contracts\Auth\Authenticable $user`
- `\Exception $exception`
