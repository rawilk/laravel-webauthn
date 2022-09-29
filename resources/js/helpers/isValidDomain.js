/**
 * MIT License
 *
 * Copyright (c) 2020 Matthew Miller
 */

/**
 * A simple test to determine if a hostname is a properly-formatted domain name.
 *
 * A "valid" domain is defined here: https://url.spec.whatwg.org/#valid-domain
 *
 * @param {string} hostname
 * @returns {boolean}
 */
export const isValidDomain = hostname => {
    return (
        // Consider localhost valid as well since it's okay with Secure Contexts.
        hostname === 'localhost' || /^([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}$/i.test(hostname)
    );
}
