<template>
    <div class="flex justify-between items-center py-1">
        <span class="text-base-content opacity-70 font-medium">{{
            label
        }}</span>
        <span class="text-base-content">{{ value }}</span>
    </div>
</template>

<script setup>
defineProps({
    label: { type: String, required: true },
    value: { type: String, required: true },
});
</script>

<style scoped>
/* aucun style supplémentaire nécessaire */
</style>
