<template>
    <section class="p-6 bg-gray-50 h-full overflow-auto">
        <!-- Onglets -->
        <div class="flex border-b mb-6">
            <button
                @click="tab = 'formations'"
                :class="tab === 'formations' ? activeClass : inactiveClass"
            >
                Formations
            </button>
            <button
                @click="tab = 'sessions'"
                :class="tab === 'sessions' ? activeClass : inactiveClass"
            >
                Sessions
            </button>
        </div>

        <!-- Contenus -->
        <div v-if="tab === 'formations'">
            <FormationList
                :api-url="routes.formationApi"
                :new-url="routes.formationNew"
            />
        </div>
        <div v-else>
            <SessionList
                :api-url="routes.sessionApi"
                :new-url="routes.sessionNew"
            />
        </div>
    </section>
</template>

<script setup>
import { ref } from "vue";
import FormationList from "./FormationList.vue";
import SessionList from "../Session/SessionList.vue";

// État de l’onglet actif
const tab = ref("formations");

// Classes Tailwind pour onglets
const activeClass = "px-4 py-2 border-b-2 border-indigo-600 text-indigo-600";
const inactiveClass = "px-4 py-2 text-gray-600 hover:text-indigo-600";

// On récupère les routes injectées par Twig
const routes = {
    formationApi: window.APP_ROUTES.formationApi,
    formationNew: window.APP_ROUTES.formationNew,
    sessionApi: window.APP_ROUTES.sessionApi,
    sessionNew: window.APP_ROUTES.sessionNew,
};
</script>
