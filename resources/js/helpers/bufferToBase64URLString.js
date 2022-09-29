/**
 * MIT License
 *
 * Copyright (c) 2020 Matthew Miller
 */

/**
 * Convert the given ArrayBuffer into a Base64URL-encoded string. Ideal for converting various
 * credential response ArrayBuffers to strings for sending back to the server as JSON.
 *
 * Helper method to compliment `base64URLStringToBuffer`
 *
 * @param {ArrayBuffer} buffer
 * @returns {string}
 */
export const bufferToBase64URLString = buffer => {
    const bytes = new Uint8Array(buffer);
    let str = '';

    for (const charCode of bytes) {
        str += String.fromCharCode(charCode);
    }

    const base64String = btoa(str);

    return base64String.replace(/\+/g, '-').replace(/\//g, '_').replace(/=/g, '');
};
