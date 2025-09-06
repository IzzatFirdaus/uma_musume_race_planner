import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        proxy: {
            "/uploads": {
                target: "http://127.0.0.1:8000",
                changeOrigin: true,
                secure: false,
            },
            "/api": {
                target: "http://127.0.0.1:8000",
                changeOrigin: true,
                secure: false,
            },
            // Optionally add /storage if you serve images from there
            // '/storage': {
            //     target: 'http://127.0.0.1:8000',
            //     changeOrigin: true,
            //     secure: false,
            // },
        },
    },
});
