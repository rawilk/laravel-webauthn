'use strict';

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
    register(publicKey, callback) {
        const publicKeyCredential = Object.assign({}, publicKey);
        publicKeyCredential.user.id = this._bufferDecode(publicKey.user.id);
        publicKeyCredential.challenge = this._bufferDecode(this._base64Decode(publicKey.challenge));
        if (publicKey.excludeCredentials) {
            publicKeyCredential.excludeCredentials = this._credentialDecode(publicKey.excludeCredentials);
        }

        navigator.credentials.create({
            publicKey: publicKeyCredential,
        }).then(data => {
            this._onRegister(data, callback);
        }, error => {
            // User probably canceled the operation.
            this._notify(error.name, error.message);
        }).catch(error => {
            this._notify('unknown', error.message);
        });
    }

    /**
     * Authenticate a user.
     *
     * @param {PublicKeyCredentialRequestOptions} publicKey
     * @param {function(PublicKeyCredential)} callback
     */
    sign(publicKey, callback) {
        const publicKeyCredential = Object.assign({}, publicKey);
        publicKeyCredential.challenge = this._bufferDecode(this._base64Decode(publicKey.challenge));
        if (publicKey.allowCredentials) {
            publicKeyCredential.allowCredentials = this._credentialDecode(publicKey.allowCredentials);
        }

        navigator.credentials.get({
            publicKey: publicKeyCredential,
        }).then(data => {
            this._onSign(data, callback);
        }, error => {
            // The user probably canceled the operation.
            this._notify(error.name, error.message);
        }).catch(error => {
            this._notify('unknown', error.message);
        });
    }

    /**
     * Test if WebAuthn is supported by this navigator.
     *
     * @returns {boolean}
     */
    supported() {
        return ! (window.PublicKeyCredential === undefined ||
            typeof window.PublicKeyCredential !== 'function' ||
            typeof window.PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable !== 'function');
    }

    notSupportedType() {
        if (! window.isSecureContext && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
            return 'notSecured';
        }

        return 'notSupported';
    }

    /**
     * @param {ArrayBuffer} value
     * @returns {Uint8Array}
     * @private
     */
    _bufferDecode(value) {
        let t = window.atob(value);

        return Uint8Array.from(t, c => c.charCodeAt(0));
    }

    /**
     * @param {ArrayBuffer} value
     * @returns {string}
     * @private
     */
    _bufferEncode(value) {
        return window.btoa(String.fromCharCode.apply(null, new Uint8Array(value)));
    }

    /**
     * Convert a base64url to a base64 string.
     *
     * @param {string} input
     * @returns {string}
     * @private
     */
    _base64Decode(input) {
        // Replace non-url compatible chars with base64 standard chars.
        input = input.replace(/-/g, '+').replace(/_/g, '/');

        // Pad out with standard base64 required padding characters.
        const pad = input.length % 4;
        if (pad) {
            if (pad === 1) {
                throw new Error('InvalidLengthError: Input base64url string is the wrong length to determine padding.');
            }

            input += new Array(5 - pad).join('=');
        }

        return input;
    }

    /**
     * Decode the given public key credentials.
     *
     * @param {PublicKeyCredentialDescriptor} credentials
     * @return {PublicKeyCredentialDescriptor}
     * @private
     */
    _credentialDecode(credentials) {
        return credentials.map(data => {
            return {
                id: this._bufferDecode(this._base64Decode(data.id)),
                type: data.type,
                transports: data.transports,
            };
        });
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

    /**
     * @param {PublicKeyCredential} publicKey
     * @param {function(PublicKeyCredential, string)} callback
     * @private
     */
    _onRegister(publicKey, callback) {
        const publicKeyCredential = {
            id: publicKey.id,
            type: publicKey.type,
            rawId: this._bufferEncode(publicKey.rawId),
            response: {
                /** @see https://www.w3.org/TR/webauthn/#authenticatorattestationresponse */
                clientDataJSON: this._bufferEncode(publicKey.response.clientDataJSON),
                attestationObject: this._bufferEncode(publicKey.response.attestationObject),
            },
        };

        callback(publicKeyCredential, this._guessDeviceName(publicKey));
    }

    /**
     * @param {PublicKeyCredential} publicKey
     * @param {function(PublicKeyCredential)} callback
     * @private
     */
    _onSign(publicKey, callback) {
        const publicKeyCredential = {
            id: publicKey.id,
            type: publicKey.type,
            rawId: this._bufferEncode(publicKey.rawId),
            response: {
                /** @see https://www.w3.org/TR/webauthn/#iface-authenticatorassertionresponse */
                authenticatorData: this._bufferEncode(publicKey.response.authenticatorData),
                clientDataJSON: this._bufferEncode(publicKey.response.clientDataJSON),
                signature: this._bufferEncode(publicKey.response.signature),
                userHandle: (publicKey.response.userHandle ? this._bufferEncode(publicKey.response.userHandle): null),
            },
        };

        callback(publicKeyCredential);
    }

    /**
     * Attempt to guess the device name the user is using as a key.
     *
     * @param {PublicKeyCredential} publicKey
     * @returns {string}
     * @private
     */
    _guessDeviceName(publicKey) {
        if (! publicKey.response.getTransports().includes('internal')) {
            return 'Security key';
        }

        const userAgent = navigator.userAgent,
              platform = navigator.platform,
              macosPlatforms = ['Macintosh', 'MacIntel', 'MacPPC', 'Mac68K'],
              windowsPlatforms = ['Win32', 'Win64', 'Windows', 'WinCE'],
              iosPlatforms = ['iPhone', 'iPad', 'iPod'];

        if (macosPlatforms.includes(platform)) {
            return 'macOS Computer';
        }

        if (iosPlatforms.includes(platform)) {
            return 'iOS Phone';
        }

        if (windowsPlatforms.includes(platform)) {
            return 'Windows Computer';
        }

        if (/Android/.test(userAgent)) {
            return 'Android Phone';
        }

        if (/Linux/.test(platform)) {
            return 'Linux Computer';
        }

        return 'Unknown Device Type';
    }
}

window.WebAuthn = WebAuthn;
