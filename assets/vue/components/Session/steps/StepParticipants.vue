<template>
    <div class="space-y-4">
        <!-- Choix de la modalité -->
        <div class="form-control">
            <label class="label">
                <span class="label-text text-base-content">Modalité</span>
            </label>
            <div class="flex space-x-6">
                <label class="label cursor-pointer flex items-center space-x-2">
                    <input
                        type="radio"
                        v-model="local.modalite"
                        value="présentiel"
                        class="radio radio-primary"
                    />
                    <span class="label-text text-base-content">Présentiel</span>
                </label>
                <label class="label cursor-pointer flex items-center space-x-2">
                    <input
                        type="radio"
                        v-model="local.modalite"
                        value="distanciel"
                        class="radio radio-primary"
                    />
                    <span class="label-text text-base-content">Distanciel</span>
                </label>
                <label class="label cursor-pointer flex items-center space-x-2">
                    <input
                        type="radio"
                        v-model="local.modalite"
                        value="hybride"
                        class="radio radio-primary"
                    />
                    <span class="label-text text-base-content">Hybride</span>
                </label>
            </div>
        </div>

        <!-- Saisie du lieu -->
        <div class="form-control">
            <label class="label">
                <span class="label-text text-base-content">Lieu</span>
            </label>
            <input
                v-model="local.lieu"
                type="text"
                placeholder="Entrez le lieu de la session"
                class="input input-bordered w-full"
            />
        </div>

        <!-- Message d'erreur -->
        <p v-if="error" class="text-error mt-1">{{ error }}</p>
    </div>
</template>

<script setup>
/**
 * Étape 4 du wizard : sélection des participants (stagiaires).
 *
 * Affiche un select multiple pour choisir les participants disponibles.
 *
 * Props :
 * - participantIds (Array) : IDs des participants sélectionnés.
 *
 * Événements :
 * - update : émis à chaque changement de sélection.
 */
import { ref, reactive, onMounted, watch } from "vue";

const props = defineProps({
    participantIds: {
        type: Array,
        default: () => [],
    },
});
const emit = defineEmits(["update"]);

// Copie locale des IDs sélectionnés
const local = reactive({ participantIds: [...props.participantIds] });

// Liste des participants disponibles
const participants = ref([]);
const error = ref("");

// Chargement des participants
onMounted(async () => {
    try {
        const res = await fetch("/api/users?role=STAGIAIRE");
        if (!res.ok) throw new Error("Échec du chargement des participants");
        participants.value = await res.json();
    } catch (e) {
        error.value = e.message;
    }
});

// Émettre les modifications vers le parent à chaque changement
watch(
    () => local.participantIds,
    (val) => emit("update", { participantIds: val }),
    { deep: true, immediate: true }
);
</script>

<style scoped>
/* DaisyUI gère les styles des form-control, label, input et radio */
</style>
