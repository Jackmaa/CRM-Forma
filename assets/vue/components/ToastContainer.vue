<!-- assets/vue/components/ToastContainer.vue -->
<template>
    <div class="fixed top-4 right-4 space-y-2 z-50">
        <div
            v-for="t in toasts"
            :key="t.id"
            class="toast alert"
            :class="t.type === 'success' ? 'alert-success' : 'alert-error'"
        >
            {{ t.message }}
        </div>
    </div>
</template>

<script setup>
import { toasts } from "../composables/useToast";
</script>

<style scoped>
.toast {
    animation: fadeIn 0.3s, fadeOut 0.3s ease-out 2.7s forwards;
}
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
@keyframes fadeOut {
    to {
        opacity: 0;
        transform: translateY(-10px);
    }
}
</style>
