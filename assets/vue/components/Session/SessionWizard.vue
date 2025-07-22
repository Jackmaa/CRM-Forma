<template>
    <div
        class="mx-auto max-w-3xl bg-base-100 shadow-lg rounded-lg p-6 space-y-6"
    >
        <!-- Barre de progression DaisyUI -->
        <div class="steps w-full">
            <div
                v-for="(step, index) in steps"
                :key="step.name"
                class="step"
                :class="{ 'step-primary': index <= currentStep }"
            >
                {{ step.name }}
            </div>
        </div>

        <!-- Titre d’étape -->
        <h2 class="text-xl font-semibold text-base-content">
            {{ steps[currentStep].name }}
        </h2>

        <!-- Composant d’étape dynamique -->
        <component
            :is="steps[currentStep].component"
            v-bind="wizardData"
            :steps="steps"
            @update="onStepUpdate"
            @edit-step="currentStep = $event"
            class="mb-6"
        />

        <!-- Navigation DaisyUI -->
        <div class="flex justify-between">
            <button
                @click="prev"
                :disabled="currentStep === 0"
                class="btn btn-outline"
            >
                Précédent
            </button>

            <button v-if="!isLast" @click="next" class="btn btn-primary">
                Suivant
            </button>
            <button
                v-else
                @click="submit"
                :disabled="submitting"
                class="btn btn-success"
            >
                <span v-if="submitting">Envoi…</span>
                <span v-else>Terminer</span>
            </button>
        </div>

        <p v-if="error" class="text-error mt-4">{{ error }}</p>
    </div>
</template>

<script setup>
/**
 * Composant wizard multi-étapes pour la création d'une session de formation.
 *
 * Affiche une barre de progression, gère la navigation entre les étapes, collecte les données et soumet la session à l'API.
 *
 * Étapes :
 * - Formation
 * - Dates
 * - Formateurs
 * - Participants
 * - Mode & Lieu
 * - Récapitulatif
 *
 * État local :
 * - currentStep : index de l'étape courante
 * - wizardData : données collectées à chaque étape
 * - submitting : état d'envoi
 * - error : message d'erreur
 */
import { ref, reactive, computed } from "vue";
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

/**
 * Met à jour les données du wizard à chaque étape.
 * @param {Object} payload
 */
function onStepUpdate(payload) {
    Object.assign(wizardData, payload);
}

/**
 * Passe à l'étape suivante.
 */
function next() {
    error.value = "";
    currentStep.value++;
}

/**
 * Revient à l'étape précédente.
 */
function prev() {
    currentStep.value--;
}

/**
 * Soumet la session à l'API à la dernière étape.
 */
async function submit() {
    submitting.value = true;
    error.value = "";
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
            error.value = Object.values(result).flat().join("\n");
            return;
        }
        window.location.href = `/session/${result.id}`;
    } catch (e) {
        error.value = e.message || "Erreur lors de la création";
    } finally {
        submitting.value = false;
    }
}
</script>

<style scoped>
/* Toutes les styles sont gérées par DaisyUI */
</style>
