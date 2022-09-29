/**
 * MIT License
 *
 * Copyright (c) 2020 Matthew Miller
 */
import { isValidDomain } from './isValidDomain';

export const errorNames = {
    Abort: 'AbortError',
    Constraint: 'ConstraintError',
    InvalidState: 'InvalidStateError',
    NotAllowed: 'NotAllowedError',
    Security: 'SecurityError',
    Type: 'TypeError',
    Unknown: 'UnknownError',
};

/**
 * Attempt to find an explanation on why an error was raised after calling `navigator.credentials.get()`.
 *
 * @param {Error} error
 * @param {CredentialRequestOptions} options
 * @return {object}
 */
export const identifyAuthenticationError = (error, options) => {
    const { publicKey } = options;

    if (! publicKey) {
        throw new Error('options param is missing the required publicKey property');
    }

    if (error.name === errorNames.Abort) {
        if (options.signal === (new AbortController).signal) {
            return {
                name: errorNames.Abort,
                default: 'Authentication ceremony was sent an abort signal.',
            };
        }
    }

    if (error.name === errorNames.NotAllowed) {
        if (publicKey.allowedCredentials?.length) {
            // https://www.w3.org/TR/webauthn-2/#sctn-discover-from-external-source (Step 17)
            // https://www.w3.org/TR/webauthn-2/#sctn-op-get-assertion (Step 6)
            return {
                name: `${errorNames.NotAllowed}_none_registered`,
                default: 'No available authenticator recognized any of the allowed credentials.',
            };
        }

        // https://www.w3.org/TR/webauthn-2/#sctn-discover-from-external-source (Step 18)
        // https://www.w3.org/TR/webauthn-2/#sctn-op-get-assertion (Step 7)
        return {
            name: errorNames.NotAllowed,
            default: 'User clicked cancel, or the authentication ceremony timed out.',
        };
    }

    if (error.name === errorNames.Security) {
        const effectiveDomain = window.location.hostname;
        if (! isValidDomain(effectiveDomain)) {
            // https://www.w3.org/TR/webauthn-2/#sctn-discover-from-external-source (Step 5)
            return {
                name: errorNames.Security,
                default: `${window.location.hostname} is an invalid domain.`,
            };
        }

        if (publicKey.rpId !== effectiveDomain) {
            return {
                name: errorNames.Security,
                default: `The RP ID "${publicKey.rpId}" is invalid for this domain.`,
            };
        }
    }

    return {
        name: errorNames.Unknown,
        default: 'The authenticator was unable to process your request.',
    };
};
