---
title: Authenticate a Key
sort: 2
---

## Introduction

When a user a WebAuthn key registered to their account, you will need to add a challenge to your authentication workflow for a second factor authentication method for the user. You can refer to the [registration docs](/docs/laravel-webauthn/{version}/basic-usage/register-key) for registering a key to a user account. In the examples shown below, we are using Alpine.js, Laravel Livewire, and TailwindCSS.

> {note} We are going to assume you already have checks in place in your authentication flows to determine if the user needs to be two-factor challenged.

## Create the Component

If you're using Laravel Livewire, you should be able to follow along with this for the most part. This should be easily adapted to other strategies if you need it, however. In this example, we are going to assume the user has already entered their username and password correctly, and your application has now redirected them to a "two-factor challenge" page. Once the page loads, we will automatically trigger the WebAuthn prompt without the user having to trigger it via a button click or something.

```php
<?php

namespace App\Http\Livewire;

use App\Http\Requests\TwoFactorLoginRequest;
use Illuminate\Support\Arr;
use Livewire\Component;
use Rawilk\Webauthn\Actions\PrepareAssertionData;
use Rawilk\Webauthn\Facades\Webauthn;

class TwoFactorChallenge extends Component
{
    /*
     * Public WebAuthn assertion key for when
     * a user has at least one key registered.
     */
    public string $publicKey = '';
    public $keyData;
    
    // See code below for request example
    public function login(TwoFactorLoginRequest $request)
    {
        $this->resetErrorBag();
        
        $user = $request->challengedUser();
        
        // WebAuthn package will update the last_used_at timestamp of the key.
        $valid = Webauthn::validateAssertion($user, Arr::only((array) $this->keyData, [
            'id',
            'rawId',
            'response',
            'type',
        ]));
        
        if (! $valid) {
            // Notify user of failed authentication attempt.
            return;
        }
        
        auth()->login($user);
        
        // Handle successful login
    }
    
    public function mount(TwoFactorLoginRequest $request): void
    {
        if (! $request->hasChallengedUser()) {
            redirect('/login');
        }
        
        $this->publicKey = json_encode(app(PrepareAssertionData::class)($request->challengedUser()));
    }
    
    public function render()
    {
        return view('livewire.two-factor-challenge');
    }
}
```

If you're following the code above, you should notice a `TwoFactorLoginRequest` request class being referenced. This request is responsible to retrieving a user account based off the ID you should be storing in the session. The sample code shown below is based off laravel/fortify and laravel/jetstream.

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TwoFactorLoginRequest extends FormRequest
{
    /** The user attempting the two factor challenge */
    protected $challengedUser;
    
    public function authorize(): bool
    {
        return true;
    }
    
    public function rules(): array
    {
        return [];
    }
    
    /**
     * Determine if there is a challenged user in the current session.
     */
    public function hasChallengedUser(): bool
    {
        $model = app(config('auth.providers.users.model'));
        
        return $this->session()->has('login.id')
            && $model::find($this->session()->get('login.id'));
    }
    
    /**
     * Get the user that is attempting the two factor challenge.
     */
    public function challengedUser()
    {
        if ($this->challengedUser) {
            return $this->challengedUser;
        }
        
        $model = app(config('auth.providers.users.model'));
        
        if (
            ! $this->session()->has('login.id')
                || ! $user = $model::find($this->session()->get('login.id'))
        ) {
            throw new \Exception('No user found.');
        }
        
        return $this->challengedUser = $user;
    }
}
```

> {note} This example is assuming you stored a session variable called `login.id` with the user's ID in a previous authentication step.

With the server side code out of the way, all that's left is rendering a view to prompt the user to insert their security key.

```html
<div>
    {{-- change "head" to a stack name you chose in your layout file. --}}
    @push('head')
        @webauthnScripts
    @endpush
    
    <div x-data="{
        publicKey: @entangle('publicKey').defer,
        keyData: @entangle('keyData').defer,
        webAuthnSupported: true,
        errorMessages: {
            NotAllowedError: {{ \Illuminate\Support\Js::from(trans('webauthn::alerts.login_not_allowed_error')) }},
            InvalidStateError: {{ \Illuminate\Support\Js::from(trans('webauthn::alerts.login_not_allowed_error')) }},
            notSupported: {{ \Illuminate\Support\Js::from(trans('webauthn::alerts.browser_not_supported')) }},
            notSecured: {{ \Illuminate\Support\Js::from(trans('webauthn::alerts.browser_not_secure')) }},
        },
        errorMessage: null,
        notifyCallback() {
            return errorName => this.errorMessage = this.errorMessages[errorName];
        },
        webAuthn: new WebAuthn,
        init() {
            this.webAuthnSupported = this.webAuthn.supported();
            if (! this.webAuthnSupported) {
                this.errorMessage = this.errorMessages[this.webAuthn.notSupportedType()];
            }
            
            this.webAuthn.registerNotifyCallback(this.notifyCallback());
            this.authenticate();
        },
        authenticate() {
            if (! this.webAuthnSupported) {
                return;
            }        
            
            this.keyData = null;
            this.errorMessage = null;
            
            // This is the most important part.
            this.webAuthn.sign(JSON.parse(this.publicKey), data => {
                this.keyData = data;
                @this.login();
            });
        },
    }">
        <div x-show="! errorMessage">
            <p>Interact with your authenticator...</p>
        </div>
        
        <div x-show="errorMessage">
            <p class="text-base text-red-600" x-html="errorMessage"></p>
            
            <div class="mt-10" x-show="webAuthnSupported">
                <button type="button"
                        class="..."
                        x-on:click="errorMessage = null; authenticate();"
                >
                    Retry
                </button>
            </div>
        </div>
    </div>
</div>
```

> {tip} Depending on your use case, it might be beneficial to offer your users an alternate challenge method (such as recovery codes) in the case they no longer have access to a security key they have registered.

Once the page loads, the user will be prompted to insert their security key to verify their identity. You may refer to [the gist](https://gist.github.com/rawilk/d9384836ea03413b3f8c572cfdf9844d) for the complete example code.
