---
title: Register a New Key
sort: 1
---

## Introduction

Before a user can use a security for authentication, they must first register it to their account. If you use the database model and client side JavaScript provided by the package, this process is fairly simple to implement. In the examples shown below, we are using Alpine.js, Laravel Livewire, and TailwindCSS.

## Prepare Attestation

```php
<?php

use Illuminate\Support\Facades\Lang;
use Livewire\Component;
use Rawilk\Webauthn\Actions\PrepareKeyCreationData;

class RegisterWebauthnKey extends Component
{
    /**
     * This will be the public key credential options required for the front-end. 
     */
    public ?string $publicKey = null;
    
    /**
     * Indicates whether or not to show the key registration modal. 
     */
    public bool $showAddKey = false;
    
   /**
    * The name for the newly registered security key.
    */
    public string $newKeyName = '';
    
   /**
    * Indicates security key instructions should be shown in the modal.
    */
    public bool $showInstructions = true;
    
    public function getErrorMessagesProperty(): array
    {
        return [
            ...Lang::get('webauthn::alerts.auth') ?? [],
            'InvalidStateError' => __('webauthn::alerts.login_not_allowed_error'),
            'notSupported' => __('webauthn::alerts.browser_not_supported'),
            'notSecured' => __('webauthn::alerts.browser_not_secure'),
        ];
    }
    
    /**
     * Show the register security key modal dialog.
     */
    public function showAddKey(): void
    {
        $this->resetErrorBag();
        
        $this->showInstructions = true;
        $this->showAddKey = true;
    }
    
    public function render()
    {
        if (! $this->publicKey) {
            $this->publicKey = json_encode(app(PrepareKeyCreationData::class)(auth()->user()));
        }    
        
        return view('livewire.register-webauthn-key');
    }
}
```

Our client-side scripts will need the `PublicKeyCredentialOptions` object that our `PrepareKeyCreationData` action will generate for you. In the example above, we are json_encoding the object so Livewire won't think the data has been tampered with whenever we re-generate a new public key, such as when the user registers a key, and we need to generate a new one to allow another key registration (more on that later).

## Render a Registration Form

In this portion, we are going to display a modal popup when the user clicks a button to begin the registration process. For brevity, we'll show some "modal" components written as blade components since they're not important for this step. Be sure to render the form in whatever UI elements you choose for you UI.

```html
@if (\Rawilk\Webauthn\Facades\Webauthn::webauthnEnabled())
@webauthnScripts

<button type="button" wire:click="showAddKey" class="...">
    Add security key
</button>

<!-- register modal -->
<div x-data="{
    keyName: @entangle('newKeyName').defer,
    showInstructions: @entangle('showInstructions').defer,
    showName: false,
    webAuthn: new WebAuthn,
    webAuthnSupported: true,
    errorMessages: {{ Js::from($this->errorMessages) }},
    errorMessage: null,
    notifyCallback() {
        return (errorName, defaultMessage) => {
            this.errorMessage = this.errorMessages[errorName] || defaultMessage;
        };
    },
    keyData: null,
    publicKey: @entangle('publicKey').defer,
    init() {
        this.webAuthnSupported = this.webAuthn.supported();
        if (! this.webAuthnSupported) {
            this.errorMessage = this.errorMessages[this.webAuthn.notSupportedType()];
        }
        
        // Register a callback when errors happen so we can notify users of the error.
        this.webAuthn.registerNotifyCallback(this.notifyCallback());
    },
    // Prompt user to register their security key.
    register() {
        if (! this.webAuthnSupported) {
            return;
        }
        
        this.errorMessage = null;
        this.keyData = null;
        this.showInstructions = false;
        this.showName = false;
        
        // This is the most important part here:
        this.webAuthn.register(JSON.parse(this.publicKey), (publicKeyCredential, deviceName) => {
            this.keyName = deviceName;
            this.keyData = publicKeyCredential;
            this.showName = true;
            setTimeout(() => this.$refs.name.focus(), 250);
        });
    },
    // Send the key data to the server.
    sendKey() {
        if (! this.keyData) {
            return;
        }
        
        @this.registerKey(this.keyData);
    },
}"
>
    <x-dialog-modal wire:model.defer="showAddKey">
        <x-slot name="title">Register Security Key</x-slot>
        
        <x-slot name="content">
            <div x-show="showInstructions && webAuthnSupported">
                <p>Some instructions on how to use a security key here...</p>
            </div>
            
            <div x-show="! showInstructions || ! webAuthnSupported">
                <!-- we are waiting for user to interact with their authenticator -->
                <div x-show="! showName && ! errorMessage && webAuthnSupported">
                    <p>Interact with your authenticator...</p>
                </div>
                
                <!-- an error has occurred (user probably canceled) -->
                <div x-show="errorMessage">
                    <p x-html="errorMessage"></p>
                    
                    <button type="button"
                            x-on:click="register"
                            class="..."
                    >
                        Retry
                    </button>
                </div>
                
                <!-- registration successful, now name key -->
                <div x-show="showName">
                    <label for="newKeyName">Name your key</label>
                    <input x-model="keyName"
                           name="newKeyName"
                           id="newKeyName"
                           required
                           x-ref="name"
                           x-on:keydown.enter.prevent.stop="keyName && sendKey"
                    >
                </div>
            </div>
        </x-slot>
        
        <x-slot name="footer">
            <!-- button to open webauthn prompt -->
            <button type="button"
                    x-show="showInstructions"
                    x-on:click="register"
                    class="..."
            >
                Next
            </button>
            
            <button type="button"
                    x-show="! showInstructions && showName"
                    x-on:click="sendKey"
                    x-bind:disabled="! keyName"
            >
                Register key
            </button>
        </x-slot>
    </x-dialog-modal>
</div>
@endif
```

In this example, the user will click on "Add security key" button, which will then open a modal dialog with some instructions on how to use a security key. When the user clicks on the "Next" button, the WebAuthn prompt will appear for the user to register their security key. If this is successful, we will request the user to name their new security key. Once the user clicks on the "Register key" button, we will send the request to the server and store the key in the database.

## Store a New Key

If you've been following the code above, you will need a `registerKey` method in the Livewire component to handle the registration of a new security key.

```php
public function registerKey($data): void
{
    $this->resetErrorBag();
    
    $this->validate([
        'newKeyName' => ['required', 'string', 'max:255'],
    ]);
    
    try {
        app(\Rawilk\Webauthn\Actions\RegisterNewKeyAction::class)(
            auth()->user(),
            \Illuminate\Support\Arr::only($data, ['id', 'rawId', 'response', 'type']),
            $this->newKeyName,
        );
    } catch (\Rawilk\Webauthn\Exceptions\WebauthnRegisterException $e) {
        $this->addError('newKeyName', $e->getMessage());
        
        return;
    }
    
    $this->publicKey = null;
    $this->showAddKey = false;
}
```

The `RegisterNewKeyAction` class will take care of verifying the signature of the key, as well as storing the security key in the database for you (see [installation](/docs/laravel-webauthn/{version}/installation#migrations)). It would also be a good idea to list out each security key the user has registered to them in the UI and also allow them to edit the key's name and/or delete each key. This however, is out of the scope of the documentation for registering a new key.

For the complete example, see the [gist](https://gist.github.com/rawilk/93ef860c06cb6d534258c18aeb472bb4).
