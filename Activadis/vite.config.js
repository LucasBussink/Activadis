import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
  plugins: [
    laravel({
      input: [
        'resources/css/app.css',
        'resources/css/layouts/app-layout.css',
        'resources/css/pages/login.css',
        'resources/css/pages/activities.css',
        'resources/css/pages/activity-form.css',
        'resources/css/pages/activity-detail.css',
        'resources/js/app.js',
      ],
      refresh: true,
    }),
  ],
});
