<template>
    <div class="p-4 bg-base-100 rounded-lg shadow-sm">
        <h3 class="text-lg font-semibold text-base-content mb-2">
            {{ title }}
        </h3>
        <div class="text-base-content opacity-90">
            <slot />
        </div>
    </div>
</template>

<script setup>
defineProps({
    title: { type: String, required: true },
});
</script>

<style scoped>
/* DaisyUI + Tailwind gèrent l’essentiel */
</style>
