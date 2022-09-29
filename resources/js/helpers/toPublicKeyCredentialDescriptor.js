/**
 * MIT License
 *
 * Copyright (c) 2020 Matthew Miller
 */

import { base64URLStringToBuffer } from './base64URLStringToBuffer';

export const toPublicKeyCredentialDescriptor = descriptor => {
    const { id } = descriptor;

    return {
        ...descriptor,
        id: base64URLStringToBuffer(id),
    }
};
