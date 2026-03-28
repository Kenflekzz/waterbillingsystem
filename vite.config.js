import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import os from 'os';

// Auto-detect local network IP
function getLocalIP() {
    const interfaces = os.networkInterfaces();
    for (const name of Object.keys(interfaces)) {
        for (const iface of interfaces[name]) {
            if (
                iface.family === 'IPv4' &&
                !iface.internal &&
                (iface.address.startsWith('192.168.') ||
                 iface.address.startsWith('172.20.10.') ||
                 iface.address.startsWith('10.'))
            ) {
                return iface.address;
            }
        }
    }
    return 'localhost'; // fallback when offline
}

const hmrHost = process.env.VITE_HMR_HOST || getLocalIP();

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
                'resources/js/flowmeter.js'
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
            host: hmrHost, // ✅ auto-detected!
            port: 5173,
        },
    },
});