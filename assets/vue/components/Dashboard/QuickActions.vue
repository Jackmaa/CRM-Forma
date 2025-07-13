<template>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Boutons principaux -->
        <button class="btn btn-outline">+ Nouveau devis</button>
        <button
            @click="openWizard"
            class="btn btn-outline flex items-center justify-center"
        >
            <span class="mr-2">📅</span>Planifier formation
        </button>

        <!-- Modal DaisyUI -->
        <div class="modal" :class="{ 'modal-open': showWizard }">
            <div class="modal-box bg-base-100 text-base-content relative">
                <!-- Bouton de fermeture -->
                <button
                    @click="closeWizard"
                    class="btn btn-sm btn-circle absolute right-2 top-2"
                >
                    ✕
                </button>
                <!-- Contenu du wizard -->
                <SessionWizard @close="closeWizard" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";
import SessionWizard from "../Session/SessionWizard.vue";

const showWizard = ref(false);

function openWizard() {
    showWizard.value = true;
}

function closeWizard() {
    showWizard.value = false;
}
</script>

<style scoped>
/* DaisyUI modal inclut déjà l'overlay et la transition */
</style>
