<template>
    <section class="p-6">
        <!-- Onglets stylés DaisyUI -->
        <div
            class="tabs tabs-boxed tabs-lifted bg-base-200 rounded-lg overflow-hidden mb-6"
        >
            <button
                class="tab flex-1 text-base-content transition-colors duration-200 hover:bg-base-300"
                :class="{
                    'tab-active bg-primary text-primary-content':
                        tab === 'formations',
                }"
                @click="setTab('formations')"
            >
                Formations
            </button>
            <button
                class="tab flex-1 text-base-content transition-colors duration-200 hover:bg-base-300"
                :class="{
                    'tab-active bg-primary text-primary-content':
                        tab === 'sessions',
                }"
                @click="setTab('sessions')"
            >
                Sessions
            </button>
        </div>

        <FormationList
            v-if="tab === 'formations'"
            :api-url="apiUrl"
            :new-url="newUrl"
        />
        <SessionList v-else :api-url="sessionApi" :new-url="sessionNew" />
    </section>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from "vue";
import FormationList from "./FormationList.vue";
import SessionList from "../Session/SessionList.vue";

const tab = ref("formations");
const props = defineProps({
    apiUrl: { type: String, required: true },
    newUrl: { type: String, required: true },
    sessionApi: { type: String, required: true },
    sessionNew: { type: String, required: true },
});

function setTab(value) {
    tab.value = value;
    location.hash = value;
}

function handleHash() {
    const h = location.hash.replace("#", "");
    if (h === "sessions" || h === "formations") {
        tab.value = h;
    }
}

onMounted(() => {
    handleHash();
    window.addEventListener("hashchange", handleHash);
});

onBeforeUnmount(() => {
    window.removeEventListener("hashchange", handleHash);
});
</script>

<style scoped>
/* Les classes DaisyUI gèrent le style, pas de CSS custom nécessaire ici */
</style>
