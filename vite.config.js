import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue(),
    ],
    server: {
        host: process.env.VITE_HOST || 'localhost',
        port: parseInt(process.env.VITE_PORT) || 5173,
        strictPort: true,
        hmr: {
            host: process.env.VITE_HOST || 'localhost',
            port: parseInt(process.env.VITE_PORT) || 5173,
        },
    },
});
