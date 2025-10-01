<template>
    <aside
        :class="[
            'flex flex-col justify-between transition-all duration-300 ease-in-out h-screen px-2 bg-base-200 overflow-x-hidden',
            isCollapsed ? 'w-16' : 'w-64',
        ]"
    >
        <div>
            <!-- Collapse toggle -->
            <button
                @click="toggleSidebar"
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
                v-show="!isCollapsed"
                class="font-bold mb-6 text-lg text-base-content px-2"
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
                        to="#"
                        class="text-error hover:bg-base-300 mb-5"
                        @click.prevent="handleLogout"
                    />
                </li>
            </ul>
        </div>
    </aside>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from "vue";
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
const userOverrode = ref(false);
const isMobile = ref(false);
const LS_KEY = "sidebar_collapsed";

onMounted(() => {
    // état sauvegardé
    const saved = localStorage.getItem(LS_KEY);
    if (saved !== null) {
        isCollapsed.value = saved === "1";
        userOverrode.value = true; // l’utilisateur a un choix
    }

    // responsive
    const mq = window.matchMedia("(max-width: 768px)");
    const apply = () => {
        isMobile.value = mq.matches;
        if (!userOverrode.value) isCollapsed.value = mq.matches;
    };
    apply();
    mq.addEventListener?.("change", apply) ?? mq.addListener(apply);

    // cleanup
    onBeforeUnmount(() => {
        mq.removeEventListener?.("change", apply) ?? mq.removeListener(apply);
    });
});

function toggleSidebar() {
    userOverrode.value = true;
    isCollapsed.value = !isCollapsed.value;
    try {
        localStorage.setItem(LS_KEY, isCollapsed.value ? "1" : "0");
    } catch {}
}

function handleLogout() {
    try {
        localStorage.removeItem("jwt_token");
        sessionStorage.removeItem("jwt_token");
    } catch (e) {
        /* no-op */
    }

    const form = document.getElementById("logout-form");
    if (form) {
        form.submit(); // ✅ POST + CSRF -> invalide la session serveur
    } else {
        // filet de sécurité si le form n'est pas présent
        window.location.href = "/logout";
    }
}

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
