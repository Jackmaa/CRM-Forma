// postcss.config.js (à la racine)
const tailwind = require("@tailwindcss/postcss");
const autoprefixer = require("autoprefixer");

module.exports = {
    plugins: [tailwind({ config: "./tailwind.config.js" }), autoprefixer()],
};
