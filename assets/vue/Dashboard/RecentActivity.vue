<template>
    <div class="bg-white rounded-lg shadow p-4">
        <h2 class="font-semibold mb-4">Activités récentes</h2>
        <ul class="space-y-3">
            <li
                v-for="item in items"
                :key="item.id"
                class="flex items-start gap-3"
            >
                <div class="p-2 bg-gray-100 rounded-full">
                    <slot name="icon" :item="item" />
                </div>
                <div>
                    <p>{{ item.title }}</p>
                    <p class="text-xs text-gray-500">{{ item.subtitle }}</p>
                </div>
            </li>
        </ul>
    </div>
</template>

<script setup>
defineProps({
    items: Array,
});
</script>
