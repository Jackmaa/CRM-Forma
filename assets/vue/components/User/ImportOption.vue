<template>
    <label
        :for="inputId"
        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer"
    >
        {{ label }}
        <input
            :id="inputId"
            type="file"
            class="hidden"
            :accept="accept"
            @change="$emit('change', $event.target.files[0])"
        />
    </label>
</template>

<script setup>
defineProps(["label", "accept", "inputId"]);
defineEmits(["change"]);
</script>
