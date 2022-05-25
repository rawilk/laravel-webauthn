<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Enabled
    |--------------------------------------------------------------------------
    |
    | Set to false to completely disable WebAuthn from this package.
    |
    */
    'enabled' => env('WEBAUTHN_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Relying Party
    |--------------------------------------------------------------------------
    |
    | We will use your application information to inform the device who is the
    | relying party. While only the name is enough, you can further set a
    | custom domain as the ID and even an icon image data encoded as base64.
    |
    */
    'relying_party' => [
        'name' => env('WEBAUTHN_NAME', env('APP_NAME')),
        'id' => env('WEBAUTHN_ID'),
        'icon' => env('WEBAUTHN_ICON'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Webauthn Challenge Length
    |--------------------------------------------------------------------------
    |
    | Length of random string used in the challenge request.
    |
    */
    'challenge_length' => 32,

    /*
    |--------------------------------------------------------------------------
    | Webauthn Timeout (milliseconds)
    |--------------------------------------------------------------------------
    |
    | Time that the caller is willing to wait for the call to complete.
    |
    */
    'timeout' => 60000,

    /*
    |--------------------------------------------------------------------------
    | Credentials Attachment
    |--------------------------------------------------------------------------
    |
    | Authentication can be tied to the current device (i.e. Windows Hello
    | or Touch ID) or a cross-platform device (USB key). When this
    | is `null`, the user will decide where to store their authentication
    | information.
    |
    | See https://www.w3.org/TR/webauthn/#enum-attachment
    |
    | Supported: `null`, `cross-platform`, `platform`
    |
    */
    'attachment_mode' => env('WEBAUTHN_ATTACHMENT_MODE'),

    /*
    |--------------------------------------------------------------------------
    | Database
    |--------------------------------------------------------------------------
    |
    | Basic configuration settings for how the package stores webauthn
    | credentials.
    |
    */
    'database' => [
        'table' => 'webauthn_keys',

        /*
         * You may either extend our model or use your own model
         * to represent a webauthn key credential.
         *
         * If you use your own model, it must implement the
         * \Rawilk\Webauthn\Contracts\WebauthnKey interface.
         */
        'model' => \Rawilk\Webauthn\Models\WebauthnKey::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Username / Email
    |--------------------------------------------------------------------------
    |
    | This value defines which model attribute should be considered as your
    | application's "username" field. Typically, this might be the email
    | address of the users, but you are free to use a different value.
    |
    */
    'username' => 'email',

    /*
    |--------------------------------------------------------------------------
    | Webauthn Public Key Credential Parameters
    |--------------------------------------------------------------------------
    |
    | List of allowed Cryptographic Algorithm Identifiers.
    | See https://www.w3.org/TR/webauthn/#sctn-alg-identifier
    |
    */
    'public_key_credential_parameters' => [
        (string) \Cose\Algorithms::COSE_ALGORITHM_ES256, // ECDSA with SHA-256
        (string) \Cose\Algorithms::COSE_ALGORITHM_ES512, // ECDSA with SHA-512
        (string) \Cose\Algorithms::COSE_ALGORITHM_RS256, // RSASSA-PKCS1-v1_5 with SHA-256
        (string) \Cose\Algorithms::COSE_ALGORITHM_EdDSA, // EdDSA
        (string) \Cose\Algorithms::COSE_ALGORITHM_ES384, // ECDSA with SHA-384
    ],

    /*
    |--------------------------------------------------------------------------
    | Webauthn Attestation Conveyance
    |--------------------------------------------------------------------------
    |
    | This parameter specifies the preference regarding the attestation conveyance
    | during credential generation.
    |
    | See https://www.w3.org/TR/webauthn/#enum-attestation-convey
    |
    | Supported: `none`, `indirect`, `direct`, `enterprise`
    |
    */
    'attestation_conveyance' => env('WEBAUTHN_ATTESTATION_CONVEYANCE', \Rawilk\Webauthn\Enums\AttestationConveyancePreference::NONE->value),

    /*
    |--------------------------------------------------------------------------
    | Google Safetynet Api Key
    |--------------------------------------------------------------------------
    |
    | Api key to use Google Safetynet when `attestation_conveyance`
    | is set to something other than `none`.
    |
    | See https://developer.android.com/training/safetynet/attestation
    |
    */
    'google_safetynet_api_key' => env('GOOGLE_SAFETYNET_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | User Presence and Verification
    |--------------------------------------------------------------------------
    |
    | Most authenticators and smartphones will ask the user to actively verify
    | themselves to log in. Use `required` to always ask to verify, `preferred`
    | to ask when possible, and `discourage` to just ask for user preference.
    |
    | See https://www.w3.org/TR/webauthn/#enum-userVerificationRequirement
    |
    | Supported: `required`, `preferred`, `discouraged`
    */
    'user_verification' => env('WEBAUTHN_USER_VERIFICATION', \Rawilk\Webauthn\Enums\UserVerification::PREFERRED->value),

    /*
    |--------------------------------------------------------------------------
    | Userless (One Touch, Typeless) Login
    |--------------------------------------------------------------------------
    |
    | By default, users must input their email to receive a list of credential
    | IDs to use for authentication, but they can also log in without specifying
    | one if the device can remember them, allowing for true one-touch login.
    |
    | If required or preferred, login verification will always be required.
    |
    | See https://www.w3.org/TR/webauthn/#enum-residentKeyRequirement
    |
    | Supported: `null`, `required`, `preferred`, `discouraged`
    |
    */
    'userless' => env('WEBAUTHN_USERLESS'),

    /*
    |--------------------------------------------------------------------------
    | Assets URL
    |--------------------------------------------------------------------------
    |
    | This value sets the path to the WebAuthn JavaScript assets for cases
    | where your app's domain root is not the correct path. By default,
    | WebAuthn will load its JavaScript assets from the app's
    | "relative root".
    |
    | Examples: "/assets", "myapp.com/app",
    |
    */
    'asset_url' => null,
];
