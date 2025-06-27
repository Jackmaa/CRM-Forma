// assets/formations.js
import { createApp } from "vue";
import Formation from "./vue/Formations/Formation.vue";
import Sidebar from "./vue/Layout/Sidebar.vue";
import "./styles/app.css";

const app = createApp({});
app.component("formation", Formation);
app.component("sidebar", Sidebar);
app.mount("#app");
