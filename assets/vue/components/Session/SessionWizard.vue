<template>
    <div class="wizard-container">
        <!-- Barre de progression -->
        <div class="flex mb-6">
            <div
                v-for="(step, index) in steps"
                :key="step.name"
                class="flex-1 h-1 mx-1 rounded"
                :class="index <= currentStep ? 'bg-blue-600' : 'bg-gray-200'"
            ></div>
        </div>

        <!-- Titre d’étape -->
        <h2 class="text-xl font-semibold mb-4">
            {{ steps[currentStep].name }}
        </h2>

        <!-- Affichage du composant d’étape -->
        <component
            :is="steps[currentStep].component"
            v-bind="wizardData"
            :steps="steps"
            @update="onStepUpdate"
            @edit-step="currentStep = $event"
            class="mb-6"
        />

        <!-- Navigation -->
        <div class="flex justify-between">
            <button
                @click="prev"
                :disabled="currentStep === 0"
                class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 disabled:opacity-50"
            >
                Précédent
            </button>

            <button
                v-if="!isLast"
                @click="next"
                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
            >
                Suivant
            </button>
            <button
                v-else
                @click="submit"
                :disabled="submitting"
                class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 disabled:opacity-50"
            >
                <span v-if="submitting">Envoi…</span>
                <span v-else>Terminer</span>
            </button>
        </div>

        <p v-if="error" class="mt-4 text-red-600">{{ error }}</p>
    </div>
</template>

<script setup>
import { ref, reactive, computed } from "vue";

// Import des étapes
import StepFormation from "./steps/StepFormation.vue";
import StepSchedule from "./steps/StepSchedule.vue";
import StepInstructors from "./steps/StepInstructors.vue";
import StepParticipants from "./steps/StepParticipants.vue";
import StepModeLocation from "./steps/StepModeLocation.vue";
import StepReview from "./steps/StepReview.vue";

const steps = [
    { name: "Formation", component: StepFormation },
    { name: "Dates", component: StepSchedule },
    { name: "Formateurs", component: StepInstructors },
    { name: "Participants", component: StepParticipants },
    { name: "Mode & Lieu", component: StepModeLocation },
    { name: "Récapitulatif", component: StepReview },
];

const currentStep = ref(0);
const submitting = ref(false);
const error = ref("");

// Données centralisées du wizard
const wizardData = reactive({
    formationId: null,
    dateDebut: null,
    dateFin: null,
    formateurIds: [],
    participantIds: [],
    modalite: "présentiel",
    lieu: "",
    remarques: "",
});

const isLast = computed(() => currentStep.value === steps.length - 1);

function onStepUpdate(payload) {
    Object.assign(wizardData, payload);
}

function next() {
    error.value = "";
    currentStep.value++;
}

function prev() {
    currentStep.value--;
}

async function submit() {
    submitting.value = true;
    error.value = "";

    // Construction du payload attendu par SessionType
    const payload = {
        formation: wizardData.formationId,
        formateur_responsable: wizardData.formateurIds[0] || null,
        dateDebut: wizardData.dateDebut,
        dateFin: wizardData.dateFin,
        modalite: wizardData.modalite,
        lieu: wizardData.lieu,
        remarques: wizardData.remarques || null,
    };

    try {
        const res = await fetch("/session/api", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload),
        });

        const result = await res.json();

        if (!res.ok) {
            console.error("Validation errors:", result);
            error.value = Object.values(result).flat().join("\n");
            return;
        }

        // Redirection vers la page de détail
        window.location.href = `/session/${result.id}`;
    } catch (e) {
        error.value = e.message || "Erreur lors de la création";
    } finally {
        submitting.value = false;
    }
}
</script>

<style scoped>
.wizard-container {
    max-width: 720px;
    margin: auto;
    background: white;
    padding: 1.5rem;
    border-radius: 0.5rem;
}
</style>
