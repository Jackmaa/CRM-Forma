<template>
    <div class="mx-auto max-w-4xl bg-base-100 shadow-lg rounded-lg p-0">
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
            @update:dateDebut="wizardData.dateDebut = $event"
            @update:dateFin="wizardData.dateFin = $event"
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
import { ref, reactive, computed, watch } from "vue";
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

const STORAGE_KEY = "session_wizard_draft_v1";
const defaults = () => ({
    formationId: null,
    dateDebut: "", // ← ISO string
    dateFin: "", // ← ISO string
    formateurIds: [],
    participantIds: [],
    modalite: "présentiel",
    lieu: "",
    remarques: "",
});

function loadDraft() {
    try {
        const raw = localStorage.getItem(STORAGE_KEY);
        if (!raw) return defaults();
        return { ...defaults(), ...JSON.parse(raw) };
    } catch {
        return defaults();
    }
}

const wizardData = reactive(loadDraft());

// autosave (debounce léger)
let t = null;
watch(
    wizardData,
    () => {
        clearTimeout(t);
        t = setTimeout(() => {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(wizardData));
        }, 150);
    },
    { deep: true }
);

function resetDraft() {
    Object.assign(wizardData, defaults());
    localStorage.removeItem(STORAGE_KEY);
}

const isLast = computed(() => currentStep.value === steps.length - 1);

// Les Steps t'envoient { ... } via @update
function onStepUpdate(payload) {
    Object.assign(wizardData, payload);
}

function next() {
    error.value = "";
    if (currentStep.value < steps.length - 1) currentStep.value++;
}
function prev() {
    if (currentStep.value > 0) currentStep.value--;
}

// ---- Soumission ----
// Conserve tes clés back existantes: formation, formateur_responsable...
async function submit() {
    submitting.value = true;
    error.value = "";

    // garde-fous dates
    if (!wizardData.dateDebut || !wizardData.dateFin) {
        error.value = "Dates requises.";
        submitting.value = false;
        return;
    }
    if (new Date(wizardData.dateFin) <= new Date(wizardData.dateDebut)) {
        error.value =
            "La date de fin doit être postérieure à la date de début.";
        submitting.value = false;
        return;
    }

    const payload = {
        formation: wizardData.formationId,
        formateur_responsable: wizardData.formateurIds[0] || null,
        dateDebut: wizardData.dateDebut, // ISO
        dateFin: wizardData.dateFin, // ISO
        modalite: wizardData.modalite,
        lieu: wizardData.lieu,
        remarques: wizardData.remarques || null,
        participants: wizardData.participantIds ?? [], // si le back l'accepte
    };

    try {
        // Si ton endpoint est bien "/session/api" (côté web), garde fetch :
        const res = await fetch("/session/api", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload),
        });
        const result = await res.json();
        if (!res.ok) {
            // agrège erreurs renvoyées par Symfony
            error.value = Array.isArray(result)
                ? result.join("\n")
                : Object.values(result || {})
                      .flat()
                      .join("\n") || "Erreur de validation.";
            return;
        }
        resetDraft();
        window.location.href = `/session/${result.id}`;
    } catch (e) {
        error.value = e?.message || "Erreur lors de la création";
    } finally {
        submitting.value = false;
    }
}
</script>

<style scoped>
/* Toutes les styles sont gérées par DaisyUI */
</style>
