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
                v-model="local.debut"
                :max="local.fin || undefined"
                aria-label="Sélectionner la date et l'heure de début"
            />

            <label class="block text-sm font-medium text-gray-700 mt-3"
                >Date et heure de fin</label
            >
            <p class="text-sm opacity-80 mb-1">
                {{ formatDate(props.dateFin) }}
            </p>
            <input
                type="datetime-local"
                class="mt-1 block w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring focus:border-blue-300"
                v-model="local.fin"
                :min="local.debut || undefined"
                aria-label="Sélectionner la date et l'heure de fin"
            />
        </div>

        <p v-if="dateError" class="text-sm text-error">{{ dateError }}</p>
    </div>
</template>

<script setup>
import { reactive, watch, computed } from "vue";

const props = defineProps({
    dateDebut: String, // ISO
    dateFin: String, // ISO
});
const emit = defineEmits(["update:dateDebut", "update:dateFin"]);

const pad = (n) => String(n).padStart(2, "0");
const toLocalInput = (iso) => {
    if (!iso) return "";
    const d = new Date(iso);
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(
        d.getDate()
    )}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
};
const fromLocalInput = (s) => (s ? new Date(s).toISOString() : "");

// ✅ état local cohérent avec le template
const local = reactive({ debut: "", fin: "" });

// ✅ watchers avec immediate pour initialiser depuis les props (y compris brouillon restoré)
watch(
    () => props.dateDebut,
    (v) => {
        local.debut = toLocalInput(v);
    },
    { immediate: true }
);
watch(
    () => props.dateFin,
    (v) => {
        local.fin = toLocalInput(v);
    },
    { immediate: true }
);

// ✅ remonter les changements en ISO vers le parent
watch(
    () => local.debut,
    (v) => emit("update:dateDebut", fromLocalInput(v))
);
watch(
    () => local.fin,
    (v) => emit("update:dateFin", fromLocalInput(v))
);

function formatDate(iso) {
    if (!iso) return "";
    const d = new Date(iso);
    return d.toLocaleString("fr-FR", {
        dateStyle: "short",
        timeStyle: "short",
    });
}

const dateError = computed(() => {
    if (!local.debut || !local.fin) return "";
    return new Date(local.fin) > new Date(local.debut)
        ? ""
        : "La date de fin doit être postérieure à la date de début.";
});
</script>

<style scoped>
/* DaisyUI gère le style de base */
</style>
