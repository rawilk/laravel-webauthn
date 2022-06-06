---
title: Model Usage
sort: 3
---

## Introduction

In addition to providing a way for users to register security keys, you should also list out their registered security keys in your UI and provide a way to edit a key's name or delete the key from their account. The model we provided in the package should suffice for most use cases, however you are free to extend ours, or use your own.

See [Models](/docs/laravel-webauthn/{version}/installation#models) for more information.

## Retrieve the keys

No matter which tech stack you use to render your UI, you may retrieve a user's registered keys via the model:

```php
$webauthnKeys = app(\Rawilk\Webauthn\Contracts\WebauthnKey::class)::query()
    ->where('user_id', auth()->user()->getAuthIdentifier())
    ->orderBy('name')
    ->get();
```

From there, you should be able to list and manipulate each key accordingly. We recommend creating a model policy to ensure a user is authorized to edit and/or delete a given WebauthnKey.

### Retrieve via Facade

You may also use the `\Rawilk\Webauthn\Facades\Webauthn` facade to retrieve a user's stored keys, however you don't have as much flexibility in the query. You will just need to pass a user instance to the facade to retrieve their keys.

```php
$webauthnKeys = \Rawilk\Webauthn\Facades\Webauthn::keysFor(auth()->user());
```

## Timestamps

In most cases, it's helpful to show a user both when a security key was registered, and when the key was last used to authenticate. When using the model provided by the package, this can easily be accomplished. Our model casts the `created_at` and `last_used_at` timestamps to Carbon instances, and also provides methods for rendering the dates inside a `<time>` html tag in a given timezone.

```html
<!-- created at -->
<div>Registered: {!! $webauthnKey->createdAtHtml('UTC') !!}</div>

<!-- last used -->
<div>Last used: {!! $webauthnKey->lastUsedAtHtml('UTC') !!}</div>
```
