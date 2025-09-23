<template>
    <div
        class="card bg-base-100 shadow-lg hover:shadow-xl transition p-4 flex items-center gap-4"
    >
        <div class="avatar">
            <div
                class="w-12 h-12 rounded-full bg-primary text-primary-content flex items-center justify-center font-bold"
            >
                {{ initials }}
            </div>
        </div>
        <div class="flex-1">
            <h3 class="text-lg font-medium text-base-content">{{ name }}</h3>
            <p class="text-sm text-base-content opacity-70">{{ email }}</p>
        </div>
        <button
            @click="$emit('view', id)"
            class="btn btn-ghost btn-sm text-success"
        >
            Voir
        </button>
    </div>
</template>
<script setup>
/**
 * Composant carte utilisateur réutilisable.
 *
 * Affiche les initiales, le nom, l'email, et un bouton "Voir".
 *
 * Props :
 * - user (Object, requis) : Données utilisateur (id, name, email, initials).
 *
 * Événements :
 * - view : émis au clic sur le bouton "Voir".
 */
import { computed } from "vue"; // ✅ indispensable

const emit = defineEmits(["view"]); // (optionnel mais propre)

const props = defineProps({
    user: { type: Object, required: true },
});

const name = computed(() => {
    const u = props.user || {};
    const fallback = [u.prenom, u.nom].filter(Boolean).join(" ").trim();
    return u.name || u.fullname || fallback;
});

const initials = computed(() => {
    const u = props.user || {};
    if (u.initials) return u.initials;
    const base =
        u.prenom && u.nom
            ? `${u.prenom} ${u.nom}`
            : u.fullname || u.email || "";
    return (
        base
            .split(/\s+/)
            .slice(0, 2)
            .map((s) => s.charAt(0).toUpperCase())
            .join("") || "??"
    );
});

const id = computed(() => props.user?.id);
const email = computed(() => props.user?.email || "");
</script>

<style scoped>
/* DaisyUI gère les styles, pas de CSS custom nécessaire */
</style>
