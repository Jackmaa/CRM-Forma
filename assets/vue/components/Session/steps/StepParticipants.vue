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
import { ref, reactive, onMounted, watch } from "vue";

const props = defineProps({
    participantIds: {
        type: Array,
        default: () => [],
    },
});
const emit = defineEmits(["update"]);

// State local pour la sélection
const local = reactive({
    participantIds: [...props.participantIds],
});

// Liste des stagiaires disponibles
const participants = ref([]);
const error = ref("");

onMounted(async () => {
    try {
        const res = await fetch("/api/users?role=STAGIAIRE");
        if (!res.ok) throw new Error("Échec du chargement des stagiaires");
        const data = await res.json();
        participants.value = data;
    } catch (e) {
        error.value = e.message;
    }
});

// Émettre les IDs sélectionnés au parent
watch(
    () => local.participantIds,
    (val) => {
        emit("update", { participantIds: val });
    },
    { deep: true }
);
</script>
