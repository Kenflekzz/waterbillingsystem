import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 
                    'resources/js/app.js',
                    'resources/css/welcome.css',
                    'resources/js/behavioral.js',
                    'resources/css/admindashboard.css',
                    'resources/css/adminloader.css',
                    'resources/css/adminsidebar.css',
                    'resources/js/adminloader.js',
                    'resources/js/payments.js',
                    'resources/js/user-billing.js',
                    'resources/js/user.js',
                    'resources/js/usernotification.js',
                    'resources/js/UserProfile.js',
                    'resources/js/reports.js',
                    'resources/js/homepage-preview.js',
                    'resources/js/clients.js',
                    'resources/css/billings.css',
                    'resources/css/admins.css',
                    'resources/css/clients.css',
                    'resources/css/adminnavbar.css',
                    'resources/css/consumption.css',
                    'resources/css/print.css',
                    'resources/css/reports.css',
                    'resources/css/user-billing.css',
                    'resources/css/usernavbar.css',
                    'resources/js/billings.js'
                    ],
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
