<template>
    <div
        class="card bg-base-100 shadow-lg hover:shadow-xl transition p-4 flex items-center gap-4"
    >
        <!-- Initiales -->
        <div class="avatar">
            <div
                class="w-12 h-12 rounded-full bg-primary text-primary-content flex items-center justify-center font-bold"
            >
                {{ initials }}
            </div>
        </div>
        <!-- Infos -->
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
const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
});

// Déstructuration des props utilisateur
const { id, name, email, initials } = props.user;
</script>

<style scoped>
/* DaisyUI gère les styles, pas de CSS custom nécessaire */
</style>
