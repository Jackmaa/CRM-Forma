import "./styles/app.css";
import "./bootstrap.js";

import { startStimulusApp } from "@symfony/stimulus-bridge";
import { registerVueControllerComponents } from "@symfony/ux-vue";
import { useTheme } from "./vue/composables/useTheme";

startStimulusApp(
    require.context(
        "@symfony/stimulus-bridge/lazy-controller-loader!./controllers",
        true,
        /\.[jt]sx?$/
    )
);

const { theme, setTheme } = useTheme();

registerVueControllerComponents(
    require.context("./vue/components", true, /\.vue$/),
    (vueApp) => {
        vueApp.provide("theme", theme);
        vueApp.provide("setTheme", setTheme);
        vueApp.config.globalProperties.$setTheme = setTheme;
    }
);
