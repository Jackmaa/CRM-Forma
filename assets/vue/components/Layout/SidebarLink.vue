<template>
    <li>
        <a
            :href="to"
            :class="[
                'flex items-center p-2 rounded-lg transition-colors',
                active
                    ? 'bg-primary text-primary-content'
                    : 'hover:bg-base-300 text-base-content',
                collapsed ? 'justify-center gap-0' : 'justify-start gap-3',
            ]"
        >
            <component :is="IconComponent" class="w-5 h-5" />
            <span
                v-if="!collapsed"
                class="ml-2 transition-opacity duration-300"
            >
                {{ label }}
            </span>
            <slot name="append" />
        </a>
    </li>
</template>

<script setup>
import { computed } from "vue";
import * as LucideIcons from "lucide-vue-next";

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
