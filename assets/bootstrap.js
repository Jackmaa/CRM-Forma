import { startStimulusApp } from "@symfony/stimulus-bridge";
import { registerVueControllerComponents } from "@symfony/ux-vue"; // AJOUTE CETTE LIGNE

startStimulusApp(
    require.context(
        "@symfony/stimulus-bridge/lazy-controller-loader!./controllers",
        true,
        /\.[jt]sx?$/
    )
);

// AJOUTE CE BLOC POUR ENREGISTRER TES COMPOSANTS VUE
registerVueControllerComponents(
    require.context("./vue/components", true, /\.vue$/)
);
