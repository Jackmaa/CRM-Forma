<template>
    <div class="grid grid-cols-2 gap-4">
        <button
            class="border-2 border-dashed border-gray-300 p-4 rounded hover:bg-gray-50 transition"
        >
            + Nouveau devis
        </button>

        <button
            class="border-2 border-dashed border-gray-300 p-4 rounded hover:bg-gray-50 transition flex items-center justify-center"
            @click="openWizard"
        >
            📅 Planifier formation
        </button>

        <!-- Modal du wizard -->
        <transition name="fade">
            <div
                v-if="showWizard"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            >
                <div
                    class="bg-white rounded-lg shadow-xl w-11/12 md:w-3/4 lg:w-1/2 p-6 relative"
                >
                    <button
                        class="absolute top-3 right-3 text-gray-500 hover:text-gray-800"
                        @click="closeWizard"
                    >
                        ✕
                    </button>
                    <SessionWizard @close="closeWizard" />
                </div>
            </div>
        </transition>
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
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
