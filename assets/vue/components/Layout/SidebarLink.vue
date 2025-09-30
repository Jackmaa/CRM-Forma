<template>
    <li>
        <!-- Lien de la barre latérale avec icône et label -->
        <a
            :href="to"
            :class="[
                'relative flex items-center h-10 rounded-lg transition-colors w-full',
                active
                    ? 'bg-primary text-primary-content'
                    : 'hover:bg-base-300 text-base-content',
                collapsed
                    ? '!justify-center px-0 gap-0'
                    : 'justify-start px-2 gap-3',
            ]"
        >
            <!-- Icône dynamique basée sur le nom passé en prop -->
            <component :is="IconComponent" class="w-5 h-5 shrink-0" />
            <span
                v-if="!collapsed"
                class="ml-2 transition-opacity duration-300 truncate"
            >
                {{ label }}
            </span>
            <!-- cache le badge quand collapsed pour éviter tout débordement -->
            <slot v-if="!collapsed" name="append" />
        </a>
    </li>
</template>

<script setup>
import { computed } from "vue";
import * as LucideIcons from "lucide-vue-next";
/*
 * Composant de lien de la barre latérale.
 *
 * Affiche un lien avec une icône et un label, adapté à l'état réduit ou étendu.
 * Utilise les icônes de Lucide pour une intégration cohérente.
 */
/*
 * Props :
 * - icon (String, requis) : Nom de l'icône Lucide à afficher.
 * - to (String, requis) : URL vers laquelle le lien pointe.
 * - label (String, requis) : Texte du lien.
 * - collapsed (Boolean, optionnel) : Indique si la barre latérale est en mode réduit.
 * - active (Boolean, optionnel) : Indique si le lien est actif.
 */
const props = defineProps({
    icon: { type: String, required: true },
    to: { type: String, required: true },
    label: { type: String, required: true },
    collapsed: { type: Boolean, default: false },
    active: { type: Boolean, default: false },
});

// Sélectionne dynamiquement l’icône Lucide correspondante
const IconComponent = computed(() => LucideIcons[props.icon] || null);
</script>

<style scoped>
/* Aucune règle custom nécessaire, DaisyUI gère le style */
</style>
