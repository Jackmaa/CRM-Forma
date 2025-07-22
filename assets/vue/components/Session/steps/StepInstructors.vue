<template>
    <div class="form-control w-full">
        <label class="label">
            <span class="label-text text-base-content">Formateurs</span>
        </label>
        <select
            v-model="local.formateurIds"
            multiple
            class="select select-bordered w-full h-32"
        >
            <option
                v-for="instructor in instructors"
                :key="instructor.id"
                :value="instructor.id"
            >
                {{
                    instructor.fullname ||
                    `${instructor.prenom} ${instructor.nom}`
                }}
            </option>
        </select>
        <p v-if="error" class="text-error mt-1">{{ error }}</p>
    </div>
</template>

<script setup>
/**
 * Étape 3 du wizard : sélection des formateurs.
 *
 * Affiche un select multiple pour choisir les formateurs disponibles.
 *
 * Props :
 * - formateurIds (Array) : IDs des formateurs sélectionnés.
 *
 * Événements :
 * - update : émis à chaque changement de sélection.
 */
import { ref, reactive, onMounted, watch } from "vue";

const props = defineProps({
    formateurIds: {
        type: Array,
        default: () => [],
    },
});
const emit = defineEmits(["update"]);

// Copie locale des IDs sélectionnés
const local = reactive({ formateurIds: [...props.formateurIds] });

// Liste des formateurs disponibles
const instructors = ref([]);
const error = ref("");

// Chargement des formateurs
onMounted(async () => {
    try {
        const res = await fetch("/api/users?role=FORMATEUR");
        if (!res.ok) throw new Error("Échec du chargement des formateurs");
        instructors.value = await res.json();
    } catch (e) {
        error.value = e.message;
    }
});

// Émettre les modifications vers le parent à chaque changement
watch(
    () => local.formateurIds,
    (val) => emit("update", { formateurIds: val }),
    { deep: true, immediate: true }
);
</script>

<style scoped>
/* DaisyUI gère le style, pas de CSS custom nécessaire */
</style>
