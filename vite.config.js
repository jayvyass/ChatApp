import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    server: {
        host: '192.168.0.116', // Your local IP address
        port: 5173, // The port Vite is using
        hmr: {
            host: '192.168.0.116', // Your local IP address
        },
    },
});
