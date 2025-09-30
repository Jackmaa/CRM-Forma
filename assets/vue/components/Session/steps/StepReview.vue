<template>
    <div class="card bg-base-100 shadow-lg p-6 space-y-4">
        <h3 class="text-lg font-medium text-base-content">
            Résumé de la session
        </h3>
        <ul
            class="list-disc list-inside space-y-1 text-base-content opacity-90"
        >
            <li>
                <span class="font-semibold">Formation :</span>
                {{ formationTitle }}
            </li>
            <li>
                <span class="font-semibold">Date de début :</span>
                {{ formatDate(dateDebut) }}
            </li>
            <li>
                <span class="font-semibold">Date de fin :</span>
                {{ formatDate(dateFin) }}
            </li>
            <li>
                <span class="font-semibold">Formateurs :</span>
                {{ selectedFormateurs }}
            </li>
            <li>
                <span class="font-semibold">Stagiaires :</span>
                {{ selectedParticipants }}
            </li>
            <li>
                <span class="font-semibold">Modalité :</span> {{ modalite }}
            </li>
            <li><span class="font-semibold">Lieu :</span> {{ lieu }}</li>
        </ul>
        <div class="flex flex-wrap gap-2 mt-4">
            <button
                v-for="(step, index) in steps.slice(0, -1)"
                :key="index"
                @click="editStep(index)"
                class="btn btn-outline btn-sm"
            >
                Modifier {{ step.name }}
            </button>
        </div>
    </div>
</template>

<script setup>
/**
 * Étape 6 du wizard : récapitulatif final avant validation.
 *
 * Affiche un résumé complet de la session et permet de revenir à une étape précédente pour modification.
 *
 * Props :
 * - formationId (Number)
 * - dateDebut (String)
 * - dateFin (String)
 * - formateurIds (Array)
 * - participantIds (Array)
 * - modalite (String)
 * - lieu (String)
 * - steps (Array) : liste des étapes du wizard
 *
 * Événements :
 * - edit-step : émis lors du clic sur un bouton "Modifier ..."
 */
import { ref, onMounted, computed } from "vue";
import { getJson } from "@/utils/apiFetch";
const props = defineProps({
    formationId: Number,
    dateDebut: String,
    dateFin: String,
    formateurIds: Array,
    participantIds: Array,
    modalite: String,
    lieu: String,
    steps: Array,
});
const emit = defineEmits(["edit-step"]);

const formationTitle = ref("");
const formateurs = ref([]);
const participants = ref([]);

// Chargement des infos de formation, formateurs et participants
onMounted(async () => {
    // Récupère le titre de la formation
    const f = await fetch(`/formation/api/${props.formationId}`).then((r) =>
        r.json()
    );
    formationTitle.value = f.title;

    // Formateurs
    if (props.formateurIds?.length) {
        formateurs.value = await Promise.all(
            props.formateurIds.map((id) => getJson(`/user/${id}`))
        );
    }

    // Participants
    if (props.participantIds?.length) {
        participants.value = await Promise.all(
            props.participantIds.map((id) => getJson(`/user/${id}`))
        );
    }
});

/**
 * Formate une date ISO en chaîne lisible (fr-FR).
 * @param {string} iso
 * @returns {string}
 */
function formatDate(iso) {
    if (!iso) return "";
    const d = new Date(iso);
    return d.toLocaleString("fr-FR", {
        dateStyle: "short",
        timeStyle: "short",
    });
}

const selectedFormateurs = computed(() =>
    formateurs.value.map((u) => `${u.prenom} ${u.nom}`).join(", ")
);
const selectedParticipants = computed(() =>
    participants.value.map((u) => `${u.prenom} ${u.nom}`).join(", ")
);

/**
 * Émet l'événement pour revenir à une étape donnée.
 * @param {number} index
 */
function editStep(index) {
    emit("edit-step", index);
}
</script>

<style scoped>
/* DaisyUI gère le style de base, pas de CSS custom nécessaire */
</style>
