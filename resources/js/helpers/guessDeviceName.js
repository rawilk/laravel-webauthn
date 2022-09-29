/**
 * Attempt to guess the device name the user is using as a key.
 *
 * @param {PublicKeyCredential} publicKey
 * @returns {string}
 */
export const guessDeviceName = publicKey => {
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
};
