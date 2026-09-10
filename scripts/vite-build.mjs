import { build } from 'vite';
import laravel from 'laravel-vite-plugin';

await build({
    configFile: false,
    root: process.cwd(),
    plugins: [laravel({
        input: ['resources/css/app.css', 'resources/js/app.js'],
        refresh: true,
    })],
    build: { emptyOutDir: true },
});
