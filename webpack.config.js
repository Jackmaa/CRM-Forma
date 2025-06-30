const Encore = require("@symfony/webpack-encore");

Encore.setOutputPath("public/build/")
    .setPublicPath("/build")
    .addEntry("app", "./assets/app.js")
    .enableStimulusBridge("./assets/controllers.json")
    .enableVueLoader()
    .enablePostCssLoader()
    .enableSingleRuntimeChunk()
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = "usage";
        config.corejs = 3;
    })
    .configureWatchOptions((watchOptions) => {
        watchOptions.poll = 1000; // Vérifie les changements toutes les 1000ms
        watchOptions.aggregateTimeout = 300;
    });

// Ici, on récupère la config
const config = Encore.getWebpackConfig();

// On force l'alias Vue runtime compiler
config.resolve.alias = {
    ...(config.resolve.alias || {}),
    vue: "vue/dist/vue.esm-bundler.js",
};

console.log("Webpack resolve.alias:", config.resolve.alias);
module.exports = config;
