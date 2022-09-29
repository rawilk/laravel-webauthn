/**
 * MIT License
 *
 * Copyright (c) 2020 Matthew Miller
 */

/**
 * A helper method to convert an arbitrary string sent from the server to an ArrayBuffer the authenticator
 * will expect.
 *
 * @param {string} value
 * @returns {Uint8Array}
 */
export const utf8StringToBuffer = value => new TextEncoder().encode(value);
