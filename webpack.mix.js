const mix = require('laravel-mix');

mix.options({
    terser: {
        extractComments: () => false,
        terserOptions: {
            compress: {
                drop_console: true,
            },
            // Prevent LICENSE.txt files being generated.
            format: {
                comments: false,
            }
        },
    },
})
    .setPublicPath('dist')
    .js('resources/js/webauthn.js', 'dist/assets')
    .version();
