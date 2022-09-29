/**
 * MIT License
 *
 * Copyright (c) 2020 Matthew Miller
 */

/**
 * Determine if the browser is capable of Webauthn.
 *
 * @returns {boolean}
 */
export const browserSupportsWebAuthn = () => window?.PublicKeyCredential !== undefined && typeof window.PublicKeyCredential === 'function';
