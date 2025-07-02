<template>
    <aside
        :class="[
            'shadow flex flex-col justify-between transition-all duration-300 ease-in-out h-screen px-2',
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

                <!-- Lien Paramètres avec badge et coloration conditionnelle -->
                <SidebarLink
                    icon="Settings"
                    label="Paramètres"
                    :collapsed="isCollapsed"
                    to="/settings"
                    :class="paramLinkClass"
                >
                    <template #append>
                        <span
                            v-if="forcePasswordReset"
                            class="absolute top-2 right-4 w-2 h-2 bg-red-600 rounded-full"
                        ></span>
                    </template>
                </SidebarLink>
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
import { ref, computed } from "vue";
import { ChevronLeft } from "lucide-vue-next";
import SidebarLink from "./SidebarLink.vue";
import { useUserSettings } from "@/composables/useUserSettings.js";

const isCollapsed = ref(false);

// On récupère forcePasswordReset depuis notre composable
const { forcePasswordReset } = useUserSettings();

// Classe conditionnelle pour le lien Paramètres
const paramLinkClass = computed(() =>
    forcePasswordReset.value
        ? "text-red-600 hover:bg-red-50 hover:text-red-700 relative"
        : ""
);
</script>
