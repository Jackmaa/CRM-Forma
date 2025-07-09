<template>
    <div class="space-y-4">
        <!-- Choix de la modalité -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1"
                >Modalité</label
            >
            <div class="flex items-center space-x-4">
                <label class="inline-flex items-center">
                    <input
                        type="radio"
                        class="form-radio h-4 w-4"
                        value="présentiel"
                        v-model="local.modalite"
                    />
                    <span class="ml-2">Présentiel</span>
                </label>
                <label class="inline-flex items-center">
                    <input
                        type="radio"
                        class="form-radio h-4 w-4"
                        value="distanciel"
                        v-model="local.modalite"
                    />
                    <span class="ml-2">Distanciel</span>
                </label>
                <label class="inline-flex items-center">
                    <input
                        type="radio"
                        class="form-radio h-4 w-4"
                        value="hybride"
                        v-model="local.modalite"
                    />
                    <span class="ml-2">Hybride</span>
                </label>
            </div>
        </div>

        <!-- Saisie du lieu -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1"
                >Lieu</label
            >
            <input
                type="text"
                class="mt-1 block w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring focus:border-blue-300"
                placeholder="Entrez le lieu de la session"
                v-model="local.lieu"
            />
        </div>

        <!-- Message d'erreur -->
        <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
    </div>
</template>

<script setup>
import { reactive, toRefs, watch, ref } from "vue";

const props = defineProps({
    modalite: {
        type: String,
        default: "présentiel",
    },
    lieu: {
        type: String,
        default: "",
    },
});
const emit = defineEmits(["update"]);

// État local pour binding
const local = reactive({
    modalite: props.modalite,
    lieu: props.lieu,
});
const error = ref("");

// Watch pour émettre les données et valider
watch(
    () => ({ modalite: local.modalite, lieu: local.lieu }),
    ({ modalite, lieu }) => {
        error.value = "";
        if (!modalite) {
            error.value = "Veuillez sélectionner une modalité.";
        } else if (!lieu.trim()) {
            error.value = "Le lieu est requis.";
        }
        emit("update", { modalite, lieu });
    },
    { deep: true, immediate: true }
);
</script>
