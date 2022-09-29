import { bufferToBase64URLString } from './bufferToBase64URLString';
import { bufferToUTF8String } from './bufferToUTF8String';

/**
 * Prepare the public key credentials object returned by the authenticator for our server.
 *
 * @param {PublicKeyCredential} credential
 * @returns {{authenticatorAttachment: *, response: {clientDataJSON: string}, rawId: string, id: string, type: string, clientExtensionResults: AuthenticationExtensionsClientOutputs}}
 */
export const preparePublicKeyCredentials = credential => {
    const { id, rawId, response, type } = credential;

    const publicKeyCredential = {
        id,
        rawId: bufferToBase64URLString(rawId),
        response: {
            clientDataJSON: bufferToBase64URLString(response.clientDataJSON),
        },
        type,
        clientExtensionResults: credential.getClientExtensionResults(),
        authenticatorAttachment: credential.authenticatorAttachment,
    };

    if (response.attestationObject !== undefined) {
        publicKeyCredential.response.attestationObject = bufferToBase64URLString(response.attestationObject);
    }

    if (response.authenticatorData !== undefined) {
        publicKeyCredential.response.authenticatorData = bufferToBase64URLString(response.authenticatorData);
    }

    if (response.signature !== undefined) {
        publicKeyCredential.response.signature = bufferToBase64URLString(response.signature);
    }

    if (response.userHandle) {
        publicKeyCredential.response.userHandle = bufferToUTF8String(response.userHandle);
    }

    return publicKeyCredential;
};
