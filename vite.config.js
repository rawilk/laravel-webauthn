import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/webauthn.js',
            ],

            refresh: false,
        }),
    ],

    build: {
        outDir: 'dist',
    },
});
