<template>
    <div class="p-4 bg-base-100 rounded-lg shadow-sm">
        <!-- En-tête de la carte -->
        <h3 class="text-lg font-semibold text-base-content mb-2">
            {{ title }}
        </h3>
        <!-- Contenu sloté, avec opacité légère pour le texte -->
        <div class="text-base-content opacity-90">
            <slot />
        </div>
    </div>
</template>

<script setup>
/**
 * Props attendues par le composant:
 * @prop {String} title - Titre de la carte (requis).
 */
defineProps({
    title: { type: String, required: true },
});
</script>

<style scoped>
/* DaisyUI + Tailwind gèrent l’essentiel: padding, background, rounded, ombre. */
</style>
