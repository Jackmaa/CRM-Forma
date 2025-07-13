<template>
    <div class="space-y-4">
        <!-- Choix de la modalité -->
        <div class="form-control">
            <label class="label">
                <span class="label-text text-base-content">Modalité</span>
            </label>
            <div class="flex space-x-6">
                <label class="label cursor-pointer flex items-center space-x-2">
                    <input
                        type="radio"
                        v-model="local.modalite"
                        value="présentiel"
                        class="radio radio-primary"
                    />
                    <span class="label-text text-base-content">Présentiel</span>
                </label>
                <label class="label cursor-pointer flex items-center space-x-2">
                    <input
                        type="radio"
                        v-model="local.modalite"
                        value="distanciel"
                        class="radio radio-primary"
                    />
                    <span class="label-text text-base-content">Distanciel</span>
                </label>
                <label class="label cursor-pointer flex items-center space-x-2">
                    <input
                        type="radio"
                        v-model="local.modalite"
                        value="hybride"
                        class="radio radio-primary"
                    />
                    <span class="label-text text-base-content">Hybride</span>
                </label>
            </div>
        </div>

        <!-- Saisie du lieu -->
        <div class="form-control">
            <label class="label">
                <span class="label-text text-base-content">Lieu</span>
            </label>
            <input
                v-model="local.lieu"
                type="text"
                placeholder="Entrez le lieu de la session"
                class="input input-bordered w-full"
            />
        </div>

        <!-- Message d'erreur -->
        <p v-if="error" class="text-error mt-1">{{ error }}</p>
    </div>
</template>

<script setup>
import { reactive, watch, ref } from "vue";

const props = defineProps({
    modalite: { type: String, default: "présentiel" },
    lieu: { type: String, default: "" },
});
const emit = defineEmits(["update"]);

// état local pour binding
const local = reactive({ modalite: props.modalite, lieu: props.lieu });
const error = ref("");

// watch pour émettre et valider
watch(
    () => [local.modalite, local.lieu],
    ([mod, lieu]) => {
        error.value = "";
        if (!mod) {
            error.value = "Veuillez sélectionner une modalité.";
        } else if (!lieu.trim()) {
            error.value = "Le lieu est requis.";
        }
        emit("update", { modalite: mod, lieu });
    },
    { immediate: true }
);
</script>

<style scoped>
/* DaisyUI gère les styles des form-control, label, input et radio */
</style>
