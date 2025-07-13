import "./styles/app.css";
import "./bootstrap.js";

import { startStimulusApp } from "@symfony/stimulus-bridge";
import { registerVueControllerComponents } from "@symfony/ux-vue";

import ToastContainer from "./vue/components/ToastContainer.vue";
import { useTheme } from "./vue/composables/useTheme";

// 1) Stimulus
startStimulusApp(
    require.context(
        "@symfony/stimulus-bridge/lazy-controller-loader!./controllers",
        true,
        /\.[jt]sx?$/
    )
);

// 2) Theme
const { theme, setTheme } = useTheme();

// 3) Vue components/controllers
registerVueControllerComponents(
    require.context("./vue/components", true, /\.vue$/),
    (vueApp) => {
        vueApp.provide("theme", theme);
        vueApp.provide("setTheme", setTheme);
        vueApp.config.globalProperties.$setTheme = setTheme;

        // Only register the ToastContainer component globally
        vueApp.component("ToastContainer", ToastContainer);
    }
);
