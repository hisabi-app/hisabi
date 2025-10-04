import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import { resolve } from 'node:path';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.tsx'],
            ssr: 'resources/js/ssr.tsx',
            refresh: true,
        }),
        react(),
        tailwindcss(),
    ],
    esbuild: {
        jsx: 'automatic',
    },
    resolve: {
        alias: {
            'ziggy-js': resolve(__dirname, 'vendor/tightenco/ziggy'),
        },
    },
    build: {
        // Disable source maps to reduce memory usage
        sourcemap: false,
        
        // Increase chunk size warning limit
        chunkSizeWarningLimit: 1000,
        
        // Optimize chunk splitting
        rollupOptions: {
            output: {
                manualChunks: {
                    // Split vendor chunks
                    'react-vendor': ['react', 'react-dom'],
                    'inertia-vendor': ['@inertiajs/react'],
                    'ui-vendor': [
                        '@radix-ui/react-avatar',
                        '@radix-ui/react-dialog',
                        '@radix-ui/react-dropdown-menu',
                        '@radix-ui/react-popover',
                        '@radix-ui/react-label',
                        '@radix-ui/react-select',
                        '@radix-ui/react-separator',
                        '@radix-ui/react-slot',
                        '@radix-ui/react-tabs',
                        '@radix-ui/react-tooltip',
                    ],
                    'chart-vendor': ['chart.js', 'react-chartjs-2'],
                    'date-vendor': ['date-fns', 'react-day-picker'],
                },
            },
        },
        
        // Reduce memory usage
        minify: 'esbuild',
        
        // Target modern browsers to reduce transformations
        target: 'es2020',
    },
});
