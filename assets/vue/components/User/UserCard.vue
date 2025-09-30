<template>
    <div
        class="bg-base-100 shadow-lg hover:shadow-xl transition p-4 flex items-center gap-4"
        @click="emit('click') || emit('view', displayId)"
    >
        <div
            class="w-12 h-12 rounded-full bg-primary text-primary-content grid place-items-center"
        >
            <span class="font-bold leading-none text-base">{{
                displayInitials
            }}</span>
        </div>
        <div class="flex-1">
            <h3 class="text-lg font-medium text-base-content">
                {{ displayName }}
            </h3>
            <p class="text-sm text-base-content opacity-70">
                {{ displayEmail }}
            </p>
        </div>
    </div>
</template>

<script setup>
/**
 * UserCard — accepte soit un objet `user`, soit des props à plat.
 * Émet `click` (compatible @click sur le composant) et `view` (avec l'id).
 */
import { computed } from "vue";

const emit = defineEmits(["click", "view"]);

const props = defineProps({
    user: { type: Object, default: null }, // optionnel
    id: [String, Number],
    name: String,
    email: String,
    initials: String,
    role: String,
    isActive: Boolean,
});

const displayId = computed(() => props.id ?? props.user?.id);

const displayName = computed(() => {
    if (props.name) return props.name;
    const u = props.user || {};
    const fallback = [u.prenom, u.nom].filter(Boolean).join(" ").trim();
    return u.name || u.fullname || fallback || u.email || "";
});

const displayEmail = computed(() => props.email ?? props.user?.email ?? "");

function makeInitials(str) {
    if (!str) return "??";
    return str
        .trim()
        .split(/\s+/)
        .slice(0, 2)
        .map((s) => s.charAt(0).toUpperCase())
        .join("");
}

const displayInitials = computed(() => {
    if (props.initials) return props.initials;
    const u = props.user || {};
    const base =
        props.name ||
        u.name ||
        u.fullname ||
        [u.prenom, u.nom].filter(Boolean).join(" ").trim() ||
        props.email ||
        u.email ||
        "";
    return makeInitials(base);
});
</script>

<style scoped>
/* DaisyUI gère le style */
</style>
