<template>
    <aside
        :class="[
            'flex flex-col justify-between transition-all duration-300 ease-in-out h-screen  bg-base-200 overflow-x-hidden',
            isCollapsed ? 'w-16' : 'w-64',
        ]"
    >
        <div>
            <!-- Collapse toggle -->
            <button
                @click="isCollapsed = !isCollapsed"
                class="btn btn-ghost btn-square w-full mb-4"
            >
                <ChevronLeft
                    :class="[
                        'transition-transform duration-300',
                        isCollapsed ? 'rotate-180' : '',
                    ]"
                />
            </button>

            <!-- Logo / Title -->
            <h1
                class="font-bold mb-6 text-lg transition-opacity duration-300 text-base-content px-2"
                :class="isCollapsed ? 'opacity-0' : 'opacity-100'"
            >
                CRM Formation
            </h1>

            <!-- Main Navigation -->
            <nav class="menu menu-vertical p-2 gap-2 text-base-content">
                <ul>
                    <li>
                        <SidebarLink
                            icon="Home"
                            label="Tableau de bord"
                            :collapsed="isCollapsed"
                            to="/"
                        />
                    </li>
                    <li>
                        <SidebarLink
                            icon="BookOpen"
                            label="Formations"
                            :collapsed="isCollapsed"
                            to="/formation"
                        />
                    </li>
                    <li>
                        <SidebarLink
                            icon="Users"
                            label="Utilisateurs"
                            :collapsed="isCollapsed"
                            to="/user"
                        />
                    </li>
                    <li>
                        <SidebarLink
                            icon="BarChart2"
                            label="Rapports"
                            :collapsed="isCollapsed"
                            to="/stats"
                        />
                    </li>
                    <li>
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
                                    class="badge badge-error badge-sm absolute right-2"
                                >
                                    1
                                </span>
                            </template>
                        </SidebarLink>
                    </li>
                </ul>
            </nav>
        </div>

        <!-- Logout at bottom -->
        <div class="p-2">
            <ul>
                <li>
                    <SidebarLink
                        icon="LogOut"
                        label="Déconnexion"
                        :collapsed="isCollapsed"
                        to="/logout"
                        class="text-error hover:bg-base-300"
                    />
                </li>
            </ul>
        </div>
    </aside>
</template>

<script setup>
import { ref, computed } from "vue";
import { ChevronLeft } from "lucide-vue-next";
import SidebarLink from "./SidebarLink.vue";
import { useUserSettings } from "@/composables/useUserSettings.js";
/**
 * Composant de la barre latérale du CRM.
 *
 * Affiche les liens de navigation principaux et gère le mode compact.
 * Inclut un badge d'alerte pour les paramètres si nécessaire.
 */
/* Props :
 * - isCollapsed (Boolean) : Indique si la barre latérale est en mode compact.
 * - forcePasswordReset (Boolean) : Indique si un changement de mot de passe est requis.
 */
const isCollapsed = ref(false);
const { forcePasswordReset } = useUserSettings();

/**
 * Classe CSS pour le lien des paramètres, change si un changement de mot de passe est requis.
 * Utilise DaisyUI pour la mise en forme.
 */
const paramLinkClass = computed(() =>
    forcePasswordReset.value
        ? "text-error relative"
        : "text-base-content hover:bg-base-300"
);
</script>

<style scoped>
/* Pas de styles spécifiques, utilisation de DaisyUI pour la mise en forme */
</style>
