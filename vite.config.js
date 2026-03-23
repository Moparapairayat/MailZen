import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { resolve } from 'path';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/automation-builder.jsx',
            ],
            refresh: true,
        }),
        react(),
    ],
    cacheDir: resolve(__dirname, '.vite'), // Use .vite in project root instead of node_modules/.vite
    server: {
        hmr: {
            host: 'localhost',
        },
        https: true,
        host: true,
    },
});

