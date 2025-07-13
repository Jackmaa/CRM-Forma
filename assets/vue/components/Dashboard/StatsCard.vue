<template>
    <div class="stats shadow-lg bg-base-100 animate-pulsing">
        <div class="stat">
            <div class="stat-title text-base-content opacity-60">
                {{ label }}
            </div>
            <div class="stat-value text-3xl font-semibold text-base-content">
                {{ value }}
            </div>
            <div class="stat-figure text-primary">
                <slot name="icon" />
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    label: { type: String, required: true },
    value: { type: [String, Number], required: true },
});
</script>

<style scoped>
/* DaisyUI gère le style, on conserve seulement l'animation */
</style>
