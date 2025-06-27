import { createApp } from "vue";
import Sidebar from "./vue/Layout/Sidebar.vue";
import StatsCard from "./vue/Dashboard/StatsCard.vue";
import PlanningCard from "./vue/Dashboard/PlanningCard.vue";
import RecentActivity from "./vue/Dashboard/RecentActivity.vue";
import QuickActions from "./vue/Dashboard/QuickActions.vue";
import "./styles/app.css";

const app = createApp({});
app.component("sidebar", Sidebar);
app.component("statscard", StatsCard);
app.component("planningcard", PlanningCard);
app.component("recentactivity", RecentActivity);
app.component("quickactions", QuickActions);
app.mount("#app");
