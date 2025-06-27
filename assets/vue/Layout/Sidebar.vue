<template>
    <aside
        :class="[
            'bg-white shadow flex flex-col justify-between transition-all duration-300 ease-in-out h-screen px-2',
            isCollapsed ? 'w-16' : 'w-64',
        ]"
    >
        <div>
            <!-- Collapse toggle -->
            <button
                @click="isCollapsed = !isCollapsed"
                class="flex items-center justify-center w-full p-2 hover:bg-gray-100"
            >
                <ChevronLeft
                    :class="[
                        'w-5 h-5 transform transition-transform duration-300',
                        isCollapsed ? 'rotate-180' : '',
                    ]"
                />
            </button>

            <h1
                class="font-bold mb-6 text-lg overflow-hidden whitespace-nowrap transition-opacity duration-300"
                :class="isCollapsed ? 'opacity-0' : 'opacity-100'"
            >
                CRM Formation
            </h1>

            <nav class="space-y-2">
                <SidebarLink
                    icon="Home"
                    label="Tableau de bord"
                    :collapsed="isCollapsed"
                    to="/"
                />
                <SidebarLink
                    icon="BookOpen"
                    label="Formations"
                    :collapsed="isCollapsed"
                    to="/formation"
                />
                <SidebarLink
                    icon="Users"
                    label="Utilisateurs"
                    :collapsed="isCollapsed"
                    to="/user"
                />
                <SidebarLink
                    icon="BarChart2"
                    label="Rapports"
                    :collapsed="isCollapsed"
                    to="/stats"
                />
                <SidebarLink
                    icon="Settings"
                    label="Paramètres"
                    :collapsed="isCollapsed"
                    to="/settings"
                />
            </nav>
        </div>

        <div class="p-2">
            <SidebarLink
                icon="LogOut"
                label="Déconnexion"
                :collapsed="isCollapsed"
                class="text-red-600 hover:bg-red-50 hover:text-red-700"
                to="/logout"
            />
        </div>
    </aside>
</template>

<script setup>
import { ref } from "vue";
import { ChevronLeft } from "lucide-vue-next";
import SidebarLink from "./SidebarLink.vue";

const isCollapsed = ref(false);
</script>
