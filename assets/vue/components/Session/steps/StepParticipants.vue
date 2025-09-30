<template>
    <div class="space-y-2">
        <label class="block text-sm font-medium text-gray-700"
            >Stagiaires</label
        >
        <select
            v-model="local.participantIds"
            multiple
            class="mt-1 block w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring focus:border-blue-300 h-32"
        >
            <option
                v-for="participant in participants"
                :key="participant.id"
                :value="participant.id"
            >
                {{
                    participant.fullname ||
                    `${participant.prenom} ${participant.nom}`
                }}
            </option>
        </select>
        <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
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
import { getJson } from "@/utils/apiFetch";

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
        const data = await getJson("/users?role=STAGIAIRE");
        const list = Array.isArray(data)
            ? data
            : data?.users ?? data?.["hydra:member"] ?? [];
        participants.value = list;
    } catch (e) {
        error.value = e?.message || "Échec du chargement des stagiaires";
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
