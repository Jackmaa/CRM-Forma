// vite.config.js (à la racine)
import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue";
import path from "path";

export default defineConfig({
    root: ".", // racine du projet
    publicDir: false, // pas de dossier public statique
    build: {
        outDir: "public/build",
        emptyOutDir: true,
        manifest: true,
        rollupOptions: {
            input: {
                // Les clés deviennent les noms de vos bundles
                dashboard: path.resolve(__dirname, "assets/dashboard.js"),
                formations: path.resolve(__dirname, "assets/formations.js"),
            },
        },
    },
    css: {
        postcss: path.resolve(__dirname, "postcss.config.js"),
    },
    resolve: {
        alias: {
            "@": path.resolve(__dirname, "assets"),
            vue: "vue/dist/vue.esm-bundler.js",
        },
    },
    plugins: [vue()],
});
