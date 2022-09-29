'use strict';

import { browserSupportsWebAuthn } from './helpers/browserSupportsWebAuthn';
import { base64URLStringToBuffer } from './helpers/base64URLStringToBuffer';
import { toPublicKeyCredentialDescriptor } from './helpers/toPublicKeyCredentialDescriptor';
import { webauthnAbortService } from './helpers/webAuthnAbortService';
import { preparePublicKeyCredentials } from './helpers/preparePublicKeyCredentials';
import { errorNames, identifyAuthenticationError } from './helpers/identifyAuthenticationError';
import { utf8StringToBuffer } from './helpers/utf8StringToBuffer';
import { guessDeviceName } from './helpers/guessDeviceName';

class WebAuthn {
    /**
     * @param {function(string, string)} notifyCallback
     */
    constructor(notifyCallback = null) {
        this.notifyCallback = notifyCallback;
    }

    /**
     * Register a notification callback.
     *
     * @param {function(string, string)} callback
     * @returns {WebAuthn}
     */
    registerNotifyCallback(callback) {
        this.notifyCallback = callback;

        return this;
    }

    /**
     * Register a new key.
     *
     * @param {PublicKeyCredentialCreationOptions} publicKey - see https://www.w3.org/TR/webauthn/#dictdef-publickeycredentialcreationoptions
     * @param {function(PublicKeyCredential)} callback  User callback
     */
    async register(publicKey, callback) {
        if (! this.supported()) {
            throw new Error('WebAuthn is not supported in this browser.');
        }

        const publicKeyCredential = Object.assign({}, publicKey);

        // We need to convert some values to Uint8Arrays before passing the credentials to the navigator.
        publicKeyCredential.challenge = base64URLStringToBuffer(publicKey.challenge);
        publicKeyCredential.user.id = utf8StringToBuffer(publicKey.user.id);
        publicKeyCredential.excludeCredentials = publicKey.excludeCredentials?.map(toPublicKeyCredentialDescriptor);

        /** @var {CredentialCreationOptions} options */
        const options = {
            publicKey: publicKeyCredential,

            // Set up the ability to cancel this request if the user attempts another.
            signal: webauthnAbortService.createNewAbortSignal(),
        };

        // Wait for the user to complete attestation.
        let credential;
        try {
            credential = await navigator.credentials.create(options);
        } catch (e) {
            const { name, default: defaultMessage } = identifyAuthenticationError(e, options);

            this._notify(name, defaultMessage);

            return;
        } finally {
            webauthnAbortService.reset();
        }

        if (! credential) {
            this._notify(errorNames.Unknown, 'Authentication was not completed.');

            return;
        }

        callback(preparePublicKeyCredentials(credential), guessDeviceName(credential));
    }

    /**
     * Authenticate a user.
     *
     * @param {PublicKeyCredentialRequestOptions} publicKey
     * @param {function(PublicKeyCredential)} callback
     */
    async sign(publicKey, callback) {
        if (! this.supported()) {
            throw new Error('WebAuthn is not supported in this browser.');
        }

        const publicKeyCredential = Object.assign({}, publicKey);

        // We need to convert some values to Uint8Arrays before passing the credentials to the navigator.
        publicKeyCredential.challenge = base64URLStringToBuffer(publicKey.challenge);
        if (publicKey.allowCredentials) {
            publicKeyCredential.allowCredentials = publicKey.allowCredentials.map(toPublicKeyCredentialDescriptor);
        }

        /** @var {CredentialCreationOptions} options */
        const options = {
            publicKey: publicKeyCredential,
            signal: webauthnAbortService.createNewAbortSignal(),
        };

        // Wait for the user to complete the assertion.
        let credential;
        try {
            credential = await navigator.credentials.get(options);
        } catch (e) {
            const { name, default: defaultMessage } = identifyAuthenticationError(e, options);

            this._notify(name, defaultMessage);

            return;
        } finally {
            webauthnAbortService.reset();
        }

        if (! credential) {
            this._notify(errorNames.Unknown, 'Authentication was not completed.');

            return;
        }

        callback(preparePublicKeyCredentials(credential));
    }

    /**
     * Test if WebAuthn is supported by this navigator.
     *
     * @returns {boolean}
     */
    supported() {
        return browserSupportsWebAuthn();
    }

    notSupportedType() {
        if (! window.isSecureContext && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
            return 'notSecured';
        }

        return 'notSupported';
    }

    /**
     * Notify end user of error if a callback is defined.
     * @param {string} name
     * @param {string} defaultMessage
     * @private
     */
    _notify(name, defaultMessage) {
        if (this.notifyCallback) {
            this.notifyCallback(name, defaultMessage);
        }
    }
}

window.WebAuthn = WebAuthn;
