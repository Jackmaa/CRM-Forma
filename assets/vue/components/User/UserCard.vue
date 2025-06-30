<template>
    <div
        class="bg-white p-4 rounded shadow hover:shadow-lg transition flex items-center gap-4"
    >
        <!-- Initiales -->
        <div
            class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center text-gray-500 text-lg font-bold"
        >
            {{ initials }}
        </div>
        <!-- Infos -->
        <div class="flex-1">
            <h3 class="text-lg font-medium">{{ name }}</h3>
            <p class="text-sm text-gray-600">{{ email }}</p>
        </div>
        <button
            @click="$emit('view', id)"
            class="text-green-600 hover:underline text-sm"
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
const { id, name, email, initials } = props.user;
</script>
