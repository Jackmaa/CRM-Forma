<template>
    <a
        :href="to"
        :class="[
            'flex items-center p-2 rounded transition-colors',
            active
                ? 'bg-blue-50 text-blue-600'
                : 'hover:bg-gray-100 text-gray-700',
            collapsed ? 'justify-center gap-0' : 'justify-start gap-3',
        ]"
    >
        <component :is="IconComponent" class="w-5 h-5" />
        <span v-if="!collapsed" class="transition-opacity duration-300">
            {{ label }}
        </span>
        <slot name="append" />
    </a>
</template>

<script setup>
import { computed } from "vue";
import * as LucideIcons from "lucide-vue-next";

/**
 * SidebarLink component
 * @component
 * @description A link for the sidebar with an icon and label.
 * @property {string} icon - The icon name from Lucide.
 * @property {string} to - The URL the link points to.
 * @property {string} label - The text label for the link.
 * @property {boolean} [collapsed=false] - Whether the sidebar is collapsed.
 * @property {boolean} [active=false] - Whether the link is currently active.
 *
 */
const props = defineProps({
    icon: { type: String, required: true },
    to: { type: String, required: true },
    label: { type: String, required: true },
    collapsed: { type: Boolean, default: false },
    active: { type: Boolean, default: false },
});

const IconComponent = computed(() => LucideIcons[props.icon] || null);
</script>
