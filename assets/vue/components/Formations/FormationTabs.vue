<template>
    <section class="p-6 bg-base-200 h-full overflow-auto">
        <!-- Onglets DaisyUI -->
        <div class="tabs mb-6">
            <button
                type="button"
                @click="tab = 'formations'"
                class="tab tab-bordered"
                :class="{ 'tab-active': tab === 'formations' }"
            >
                Formations
            </button>
            <button
                type="button"
                @click="tab = 'sessions'"
                class="tab tab-bordered"
                :class="{ 'tab-active': tab === 'sessions' }"
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

// Routes injectées par Twig (window.APP_ROUTES)
const routes = {
    formationApi: window.APP_ROUTES.formationApi,
    formationNew: window.APP_ROUTES.formationNew,
    sessionApi: window.APP_ROUTES.sessionApi,
    sessionNew: window.APP_ROUTES.sessionNew,
};
</script>

<style scoped>
/* DaisyUI tabs gèrent déjà le style et la transition */
</style>
