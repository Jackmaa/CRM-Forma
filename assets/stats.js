import { createApp } from "vue";
import Stat from "./vue/Stats/Stat.vue";
import "./styles/app.css";
import Sidebar from "./vue/Layout/Sidebar.vue";

const app = createApp({});
app.component("stat", Stat);
app.component("sidebar", Sidebar);
app.mount("#app");
