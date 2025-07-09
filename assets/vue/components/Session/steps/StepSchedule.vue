<template>
    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700"
                >Date et heure de début</label
            >
            <input
                type="datetime-local"
                class="mt-1 block w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring focus:border-blue-300"
                v-model="local.dateDebut"
            />
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700"
                >Date et heure de fin</label
            >
            <input
                type="datetime-local"
                class="mt-1 block w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring focus:border-blue-300"
                v-model="local.dateFin"
            />
        </div>
        <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
    </div>
</template>

<script setup>
import { reactive, ref, watch } from "vue";

const props = defineProps({
    dateDebut: String,
    dateFin: String,
});
const emit = defineEmits(["update"]);

// Local state to bind to datetime-local inputs (format: YYYY-MM-DDThh:mm)
const local = reactive({
    dateDebut: props.dateDebut || "",
    dateFin: props.dateFin || "",
});

const error = ref("");

// Watch local changes and emit only valid pairs
watch(
    () => ({ dateDebut: local.dateDebut, dateFin: local.dateFin }),
    ({ dateDebut, dateFin }) => {
        error.value = "";
        if (dateDebut && dateFin && dateFin < dateDebut) {
            error.value =
                "La date de fin doit être postérieure à la date de début";
        }
        emit("update", {
            dateDebut,
            dateFin,
        });
    },
    { deep: true }
);
</script>
