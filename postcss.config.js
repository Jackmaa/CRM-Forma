// postcss.config.js
module.exports = {
    plugins: [
        require("@tailwindcss/postcss"), // c’est LE plugin PostCSS pour Tailwind v4+
        require("autoprefixer"),
    ],
};
