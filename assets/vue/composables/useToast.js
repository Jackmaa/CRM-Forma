// src/vue/composables/useToast.js
import { reactive } from "vue";

export const toasts = reactive([]);

// low-level helper
function addToast({ message, type = "success", duration = 3000 }) {
    const id = Date.now() + Math.random();
    toasts.push({ id, message, type });
    setTimeout(() => {
        const i = toasts.findIndex((t) => t.id === id);
        if (i > -1) toasts.splice(i, 1);
    }, duration);
}

export function toastSuccess(message, duration) {
    addToast({ message, type: "success", duration });
}
export function toastError(message, duration) {
    addToast({ message, type: "error", duration });
}

// the singleton API you’ll import directly:
export const toast = {
    success: toastSuccess,
    error: toastError,
};
