// assets/app.js

// 1) Vos styles (import statique OK, il ne déclenche pas Stimulus)
import "./styles/app.css";

// 2) On attend que window.APP_USER_ROLES soit déjà défini dans le <head> Twig
//    (vous avez bien injecté votre script dans le head avant les tags Encore)

// 3) Configurez UX-Vue *impérativement* avec require()
const { registerVueControllerComponents } = require("@symfony/ux-vue");
const { computed } = require("vue");
const { useTheme } = require("./vue/composables/useTheme");
const ToastContainer = require("./vue/components/ToastContainer.vue").default;

const { theme, setTheme } = useTheme();

registerVueControllerComponents(
    require.context("./vue/components", true, /\.vue$/),
    (vueApp) => {
        // → API Thème
        vueApp.provide("theme", theme);
        vueApp.provide("setTheme", setTheme);

        // → ToastContainer global
        vueApp.component("ToastContainer", ToastContainer);

        // → Permissions
        const roles = window.APP_USER_ROLES || [];
        vueApp.provide("userRoles", roles);
        vueApp.provide(
            "isStagiaire",
            computed(() => roles.includes("ROLE_STAGIAIRE"))
        );
        vueApp.provide(
            "isAdmin",
            computed(() => roles.includes("ROLE_ADMIN_CENTRE"))
        );
    }
);

// 4) Enfin, démarrez Stimulus (bootstrap.js contient startStimulusApp)
require("./bootstrap.js");
