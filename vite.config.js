import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';

// Safe HMR host for both local and Docker
const hmrHost = process.env.VITE_HMR_HOST || 'localhost';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
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
                'resources/js/billings.js',
                'resources/css/user_receipt.css',
                'resources/js/flowmeter.js',
                'resources/css/user.css',
            ],
            refresh: true,
        }),
        vue(),
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        cors: true,
        hmr: {
            host: hmrHost,
            port: 5173,
        },
    },
    // Add this for production build safety
    build: {
        outDir: 'public/build',
        emptyOutDir: true,
    },
});