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
/**
 * Étape 1 du wizard : sélection de la formation.
 *
 * Affiche un select pour choisir la formation parmi celles disponibles.
 *
 * Props :
 * - formationId (Number) : ID de la formation sélectionnée (optionnel).
 *
 * Événements :
 * - update : émis à chaque changement de sélection.
 */
import { ref, onMounted, watch } from "vue";

const props = defineProps({ formationId: Number });
const emit = defineEmits(["update"]);

// État local pour v-model
const local = ref({ formationId: props.formationId });
const formations = ref([]);

// Chargement des données de formation
onMounted(async () => {
    try {
        formations.value = await fetch("/formation/api").then((r) => r.json());
    } catch {
        formations.value = [];
    }
});

// Émettre l'update au parent à chaque changement
watch(
    () => local.value.formationId,
    (val) => emit("update", { formationId: val }),
    { immediate: true }
);
</script>

<style scoped>
/* DaisyUI gère le style des sélecteurs et labels */
</style>
