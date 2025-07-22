// src/vue/composables/useToast.js
import { reactive } from "vue";

export const toasts = reactive([]);

// Fonction pour ajouter un toast
/**
 * @typedef {Object} Toast
 * @property {number} id - Identifiant unique du toast
 * @property {string} message - Message du toast
 * @property {string} type - Type du toast (success, error, etc.)
 * @property {number} duration - Durée d'affichage du toast en millisecondes
 * @param {Object} options - Options pour le toast
 * @param {string} options.message - Message à afficher dans le toast
 * @param {string} [options.type="success"] - Type du toast (success, error, etc.)
 * @param {number} [options.duration=3000] - Durée d'affichage du toast en millisecondes
 */
function addToast({ message, type = "success", duration = 3000 }) {
    const id = Date.now() + Math.random();
    toasts.push({ id, message, type });
    setTimeout(() => {
        const i = toasts.findIndex((t) => t.id === id);
        if (i > -1) toasts.splice(i, 1);
    }, duration);
}

// Fonctions pour les toasts de succès et d'erreur
export function toastSuccess(message, duration) {
    addToast({ message, type: "success", duration });
}

// Fonction pour les toasts d'erreur
export function toastError(message, duration) {
    addToast({ message, type: "error", duration });
}

// Fonctions pour les toasts
export const toast = {
    success: toastSuccess,
    error: toastError,
};
