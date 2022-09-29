/**
 * MIT License
 *
 * Copyright (c) 2020 Matthew Miller
 */

/**
 * Provides a way to cancel an existing WebAuthn request, for example to cancel a
 * WebAuthn autofill authentication request for a manual authentication attempt.
 */
class WebAuthnAbortService {
    constructor() {
        this.reset();
    }

    /**
     * Prepare an abort signal that will help support multiple auth attempts without needing to
     * reload the page.
     *
     * @returns {AbortSignal}
     */
    createNewAbortSignal() {
        // Abort any existing calls to navigator.credentials.create() or navigator.credentials.get().
        if (this.controller) {
            this.controller.abort();
        }

        this.controller = new AbortController;

        return this.controller.signal;
    }

    reset() {
        this.controller = undefined;
    }
}

export const webauthnAbortService = new WebAuthnAbortService;
