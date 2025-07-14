// assets/app.js

// 1) Import CSS & Stimulus
import "./styles/app.css";
import "./bootstrap.js";

// 2) Démarrage de Stimulus
import { startStimulusApp } from "@symfony/stimulus-bridge";
startStimulusApp(
    require.context(
        "@symfony/stimulus-bridge/lazy-controller-loader!./controllers",
        true,
        /\.[jt]sx?$/
    )
);

// 3) Setup Vue via Symfony UX Vue
import { registerVueControllerComponents } from "@symfony/ux-vue";
import { useTheme } from "./vue/composables/useTheme";
import ToastContainer from "./vue/components/ToastContainer.vue";

// Récupération du thème global
const { theme, setTheme } = useTheme();

registerVueControllerComponents(
    require.context("./vue/components", true, /\.vue$/),
    (vueApp) => {
        // Provide global theme functions
        vueApp.provide("theme", theme);
        vueApp.provide("setTheme", setTheme);
        vueApp.config.globalProperties.$setTheme = setTheme;

        // Provide userRoles extracted from Twig
        // insère avant build <script>window.APP_USER_ROLES = {{ app.user.roles|json_encode|raw }};</script>
        vueApp.provide("userRoles", window.APP_USER_ROLES);

        // Register global ToastContainer
        vueApp.component("ToastContainer", ToastContainer);
    }
);
