import { createApp } from "vue";
import UserPage from "./vue/User/User.vue";
import "./styles/app.css";
import Sidebar from "./vue/Layout/Sidebar.vue";

const app = createApp({});
app.component("userpage", UserPage);
app.component("sidebar", Sidebar);
app.mount("#app");
