/**
 * MIT License
 *
 * Copyright (c) 2020 Matthew Miller
 */

/**
 * A helper method to convert an arbitrary ArrayBuffer returned from an authenticator to a UTF-8
 * string.
 *
 * @param {ArrayBuffer} value
 * @returns {string}
 */
export const bufferToUTF8String = value => new TextDecoder('utf-8').decode(value);
