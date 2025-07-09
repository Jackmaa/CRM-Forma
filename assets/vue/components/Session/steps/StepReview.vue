<template>
    <div class="space-y-4">
        <h3 class="text-lg font-medium">Résumé de la session</h3>
        <ul class="list-disc list-inside space-y-1">
            <li><strong>Formation :</strong> {{ formationTitle }}</li>
            <li>
                <strong>Date de début :</strong> {{ formatDate(dateDebut) }}
            </li>
            <li><strong>Date de fin :</strong> {{ formatDate(dateFin) }}</li>
            <li><strong>Formateurs :</strong> {{ selectedFormateurs }}</li>
            <li><strong>Stagiaires :</strong> {{ selectedParticipants }}</li>
            <li><strong>Modalité :</strong> {{ modalite }}</li>
            <li><strong>Lieu :</strong> {{ lieu }}</li>
        </ul>
        <div class="flex flex-wrap gap-2 mt-4">
            <!-- Exclure la dernière étape (récapitulatif) des boutons de modification -->
            <button
                v-for="(step, index) in steps.slice(0, -1)"
                :key="index"
                @click="editStep(index)"
                class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300 text-sm"
            >
                Modifier {{ step.name }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";

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
const emit = defineEmits(["update", "edit-step"]);

const formationTitle = ref("");
const formateurs = ref([]);
const participants = ref([]);

// Fetch labels for formation, formateurs, participants
onMounted(async () => {
    // Formation
    const f = await fetch(`/formation/api/${props.formationId}`).then((r) =>
        r.json()
    );
    formationTitle.value = f.title;

    // Formateurs
    if (props.formateurIds.length) {
        formateurs.value = await Promise.all(
            props.formateurIds.map((id) =>
                fetch(`/api/user/${id}`).then((r) => r.json())
            )
        );
    }

    // Participants
    if (props.participantIds.length) {
        participants.value = await Promise.all(
            props.participantIds.map((id) =>
                fetch(`/api/user/${id}`).then((r) => r.json())
            )
        );
    }
});

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

function editStep(index) {
    emit("edit-step", index);
}
</script>
