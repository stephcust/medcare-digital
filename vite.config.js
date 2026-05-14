import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import run from 'vite-plugin-run';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        run({
            name: 'Ziggy Routes Types',
            run: ['php', 'artisan', 'ziggy:generate', '--types-only'],
            pattern: 'routes/**/*.php',
            startup: true,
            build: false,
            silent: false,
        }),
        run({
            name: 'Eloquent Model Types',
            run: ['php', 'artisan', 'types:generate', '--outputDir=resources/js/Assets/ModelTypes'],
            pattern: 'app/Models/**/*.php',
            startup: true,
            build: false,
            silent: false,
        }),
    ],
});
