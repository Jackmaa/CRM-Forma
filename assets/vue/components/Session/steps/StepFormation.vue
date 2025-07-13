<template>
    <div class="form-control w-full">
        <label class="label">
            <span class="label-text text-base-content"
                >Choisir la formation</span
            >
        </label>
        <select
            v-model="local.formationId"
            class="select select-bordered w-full"
        >
            <option :value="null" disabled>-- Sélectionnez --</option>
            <option v-for="f in formations" :key="f.id" :value="f.id">
                {{ f.title }}
            </option>
        </select>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from "vue";

const props = defineProps({ formationId: Number });
const emit = defineEmits(["update"]);

// local state pour v-model
const local = ref({ formationId: props.formationId });
const formations = ref([]);

// Chargement des données
onMounted(async () => {
    try {
        formations.value = await fetch("/formation/api").then((r) => r.json());
    } catch {
        formations.value = [];
    }
});

// Émettre l’update au parent
watch(
    () => local.value.formationId,
    (val) => emit("update", { formationId: val }),
    { immediate: true }
);
</script>

<style scoped>
/* DaisyUI gère le style des sélecteurs et labels */
</style>
