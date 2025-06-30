<template>
    <div
        class="bg-white rounded-lg shadow p-4 flex items-center justify-between animate-pulsing"
    >
        <div>
            <p class="text-sm text-gray-500">{{ label }}</p>
            <p class="text-2xl font-semibold">{{ value }}</p>
        </div>
        <div class="p-2 bg-blue-50 rounded-full">
            <slot name="icon" />
        </div>
    </div>
</template>

<script setup>
defineProps({
    label: String,
    value: [String, Number],
});
</script>
