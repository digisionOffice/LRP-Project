import { defineConfig } from 'vite'
import laravel, { refreshPaths } from 'laravel-vite-plugin'

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/filament/admin/theme.css',
                'resources/js/geolocation.js',
                'resources/js/camera-upload.js',
                'resources/js/karyawan-geolocation.js',
            ],
            refresh: [
                ...refreshPaths,
                'app/Livewire/**',
                'routes/**',
            ],
        }),
    ],
})
