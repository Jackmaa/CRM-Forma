import { createApp } from "vue";
import Sidebar from "./vue/Layout/Sidebar.vue";
import Settings from "./vue/Settings/Settings.vue";
import "./styles/app.css";

const app = createApp({});
app.component("sidebar", Sidebar);
app.component("settings", Settings);
app.mount("#app");
