import { createServer } from 'vite';
import laravel from 'laravel-vite-plugin';

const server = await createServer({
    configFile: false,
    root: process.cwd(),
    plugins: [laravel({
        input: ['resources/css/app.css', 'resources/js/app.js'],
        refresh: true,
    })],
});

await server.listen();
server.printUrls();
