<template>
    <div class="card bg-base-100 shadow-lg p-6 space-y-4">
        <div class="form-control">
            <label class="block text-sm font-medium text-gray-700"
                >Date et heure de début</label
            >
            <p class="text-sm opacity-80 mb-1">
                {{ formatDate(props.dateDebut) }}
            </p>
            <input
                type="datetime-local"
                class="mt-1 block w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring focus:border-blue-300"
                v-model="local.dateDebut"
                :max="local.dateFin || undefined"
                aria-label="Sélectionner la date et l'heure de début"
            />

            <label class="block text-sm font-medium text-gray-700"
                >Date et heure de fin</label
            >
            <p class="text-sm opacity-80 mb-1">
                {{ formatDate(props.dateFin) }}
            </p>
            <input
                type="datetime-local"
                class="mt-1 block w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring focus:border-blue-300"
                v-model="local.dateFin"
                :min="local.dateDebut || undefined"
                aria-label="Sélectionner la date et l'heure de fin"
            />
        </div>

        <p v-if="dateError" class="text-sm text-error">{{ dateError }}</p>
    </div>
</template>

<script setup>
/**
 * Étape 2 du wizard : récapitulatif + sélection/édition des dates.
 * - Les inputs <datetime-local> modifient un état local et émettent des updates en ISO.
 * - Utilise v-model:dateDebut et v-model:dateFin côté parent.
 */
import { ref, onMounted, computed, reactive, watch } from "vue";

const props = defineProps({
    formationId: Number,
    dateDebut: String, // ISO (ex: 2025-09-30T08:00:00.000Z)
    dateFin: String, // ISO
    formateurIds: Array,
    participantIds: Array,
    modalite: String,
    lieu: String,
    steps: Array,
});

const emit = defineEmits(["edit-step", "update:dateDebut", "update:dateFin"]);

const formationTitle = ref("");
const formateurs = ref([]);
const participants = ref([]);

// ---- Helpers pour <input type="datetime-local"> ----
function pad(n) {
    return String(n).padStart(2, "0");
}

/** ISO -> 'YYYY-MM-DDTHH:mm' (local) */
function toLocalInput(iso) {
    if (!iso) return "";
    const d = new Date(iso);
    const y = d.getFullYear();
    const m = pad(d.getMonth() + 1);
    const day = pad(d.getDate());
    const hh = pad(d.getHours());
    const mm = pad(d.getMinutes());
    return `${y}-${m}-${day}T${hh}:${mm}`;
}

/** 'YYYY-MM-DDTHH:mm' (local) -> ISO (UTC) */
function fromLocalInput(localStr) {
    if (!localStr) return "";
    const d = new Date(localStr); // interprété en local par le navigateur
    return d.toISOString();
}

/** Affichage lisible */
function formatDate(iso) {
    if (!iso) return "";
    const d = new Date(iso);
    return d.toLocaleString("fr-FR", {
        dateStyle: "short",
        timeStyle: "short",
    });
}

// ---- État local pour les inputs ----
const local = reactive({
    dateDebut: toLocalInput(props.dateDebut),
    dateFin: toLocalInput(props.dateFin),
});

// Si le parent change les props, on resynchronise l’UI
watch(
    () => props.dateDebut,
    (v) => {
        local.dateDebut = toLocalInput(v);
    }
);
watch(
    () => props.dateFin,
    (v) => {
        local.dateFin = toLocalInput(v);
    }
);

// Quand l’utilisateur modifie l’UI, on émet les updates vers le parent
watch(
    () => local.dateDebut,
    (v) => {
        emit("update:dateDebut", fromLocalInput(v));
    }
);
watch(
    () => local.dateFin,
    (v) => {
        emit("update:dateFin", fromLocalInput(v));
    }
);

// Validation simple début < fin
const dateError = computed(() => {
    if (!local.dateDebut || !local.dateFin) return "";
    const start = new Date(local.dateDebut).getTime();
    const end = new Date(local.dateFin).getTime();
    return end > start
        ? ""
        : "La date de fin doit être postérieure à la date de début.";
});

// Chargement des infos distantes
onMounted(async () => {
    // Titre formation
    const f = await fetch(`/formation/api/${props.formationId}`).then((r) =>
        r.json()
    );
    formationTitle.value = f.title;

    // Formateurs
    if (props.formateurIds?.length) {
        formateurs.value = await Promise.all(
            props.formateurIds.map((id) =>
                fetch(`/api/user/${id}`).then((r) => r.json())
            )
        );
    }
    // Participants
    if (props.participantIds?.length) {
        participants.value = await Promise.all(
            props.participantIds.map((id) =>
                fetch(`/api/user/${id}`).then((r) => r.json())
            )
        );
    }
});

const selectedFormateurs = computed(() =>
    formateurs.value.map((u) => `${u.prenom} ${u.nom}`).join(", ")
);
const selectedParticipants = computed(() =>
    participants.value.map((u) => `${u.prenom} ${u.nom}`).join(", ")
);

function editStep(index) {
    emit("edit-step", index);
}
</script>

<style scoped>
/* DaisyUI gère le style de base, pas de CSS custom nécessaire */
</style>
