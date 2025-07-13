// tailwind.config.js
module.exports = {
    content: ["./templates/**/*.html.twig", "./assets/**/*.{vue,js}"],
    theme: {
        extend: {},
    },
    plugins: [require("daisyui")],
    daisyui: {
        themes: [
            "light", // thème « clair » standard
            "dark", // thème « sombre » standard
            // tu peux ajouter des thèmes custom ici, par exemple :
            // {
            //   mytheme: {
            //     'primary': '#4a90e2',
            //     'secondary': '#f000b8',
            //     // …
            //   }
            // }
        ],
        darkTheme: "dark", // nom du thème sombre
    },
};
