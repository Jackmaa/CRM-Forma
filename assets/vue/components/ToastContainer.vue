<!-- assets/vue/components/ToastContainer.vue -->

<template>
    <!-- Conteneur fixe pour les notifications, positionné en haut à droite -->
    <div class="fixed top-4 right-4 space-y-2 z-50">
        <!-- Itération sur chaque toast fourni par le composable useToast -->
        <div
            v-for="t in toasts"
            :key="t.id"
            class="toast alert"
            :class="t.type === 'success' ? 'alert-success' : 'alert-error'"
        >
            <!-- Affiche le message de la notification -->
            {{ t.message }}
        </div>
    </div>
</template>

<script setup>
// Import du tableau réactif 'toasts' depuis le composable qui gère les notifications
import { toasts } from "../composables/useToast";
</script>

<style scoped>
/* Animation d'apparition et de disparition pour chaque toast */
.toast {
    /* fadeIn sur 0.3s puis fadeOut après 2.7s, avec easing pour la disparition */
    animation: fadeIn 0.3s, fadeOut 0.3s ease-out 2.7s forwards;
}

/* Animation d'entrée : opacity de 0 à 1 et translation verticale */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Animation de sortie : opacity à 0 et retour vers le haut */
@keyframes fadeOut {
    to {
        opacity: 0;
        transform: translateY(-10px);
    }
}
</style>
