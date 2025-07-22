<template>
    <div class="flex justify-between items-center py-1">
        <!-- Label avec opacité réduite et poids de police moyen -->
        <span class="text-base-content opacity-70 font-medium">{{
            label
        }}</span>
        <!-- Valeur affichée normalement -->
        <span class="text-base-content">{{ value }}</span>
    </div>
</template>

<script setup>
/**
 * Props attendues par le composant:
 * @prop {String} label - Texte du label (requis).
 * @prop {String} value - Texte de la valeur (requis).
 */
defineProps({
    label: { type: String, required: true },
    value: { type: String, required: true },
});
</script>

<style scoped>
/* Aucun style supplémentaire nécessaire: mise en forme gérée par les classes Tailwind/DaisyUI. */
</style>
