<template>
    <div class="form-control w-full">
        <label class="label">
            <span class="label-text text-base-content">{{ label }}</span>
        </label>
        <div>
            <slot />
        </div>
        <p v-if="error" class="text-xs text-error mt-1">{{ error }}</p>
    </div>
</template>

<script setup>
import { toRefs } from "vue";

const props = defineProps({
    label: { type: String, required: true },
    error: { type: String, default: "" },
});
const { error } = toRefs(props);
</script>

<style scoped>
/* rien d’autre à ajouter */
</style>
