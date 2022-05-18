const mix = require('laravel-mix');

mix.setPublicPath('dist');

mix.js('resources/js/webauthn.js', 'webauthn.js')
    .sourceMaps(false)
    .version()
    .disableSuccessNotifications();
