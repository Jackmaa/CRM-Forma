<template>
    <div class="form-control w-full">
        <!-- Affiche le label passé en prop -->
        <label class="label">
            <span class="label-text text-base-content">{{ label }}</span>
        </label>
        <div>
            <!-- Slot pour injecter l'élément de formulaire personnalisé -->
            <slot />
        </div>
        <!-- Affichage d'un message d'erreur si présent -->
        <p v-if="error" class="text-xs text-error mt-1">{{ error }}</p>
    </div>
</template>

<script setup>
import { toRefs } from "vue";

/**
 * Props attendues par le composant:
 * @prop {String} label - Texte du label (requis).
 * @prop {String} error - Message d'erreur (par défaut : chaine vide).
 */
const props = defineProps({
    label: { type: String, required: true },
    error: { type: String, default: "" },
});
// Déstructure la prop error pour la rendre réactive
const { error } = toRefs(props);
</script>

<style scoped>
/* Pas de styles custom: utilisation de Tailwind/DaisyUI pour la mise en forme. */
</style>
