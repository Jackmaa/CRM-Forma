<template>
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-4">Activités récentes</h2>

        <!-- Skeleton loader -->
        <div v-if="loading" class="space-y-2 animate-pulse">
            <div class="h-4 bg-gray-200 rounded w-3/4"></div>
            <div class="h-4 bg-gray-200 rounded w-2/3"></div>
            <div class="h-4 bg-gray-200 rounded w-5/6"></div>
        </div>

        <!-- Message d'erreur -->
        <p v-else-if="error" class="text-red-500">{{ error }}</p>

        <!-- Liste animée -->
        <transition-group
            name="list"
            tag="ul"
            class="space-y-3"
            v-else-if="items.length"
        >
            <li
                v-for="item in items"
                :key="item.id"
                class="flex items-start gap-3 p-2 hover:bg-gray-50 rounded transition"
            >
                <div class="p-2 bg-gray-100 rounded-full flex-shrink-0">
                    <!-- Icônes dynamiques -->
                    <svg
                        v-if="item.action === 'delete'"
                        xmlns="http://www.w3.org/2000/svg"
                        class="w-5 h-5 text-red-500"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M6 2a1 1 0 00-1 1v1H3v2h14V4h-2V3a1 1 0 00-1-1H6zM4 7v9a2 2 0 002 2h8a2 2 0 002-2V7H4z"
                            clip-rule="evenodd"
                        />
                    </svg>
                    <svg
                        v-else-if="item.action === 'create'"
                        xmlns="http://www.w3.org/2000/svg"
                        class="w-5 h-5 text-green-500"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                    >
                        <path
                            d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                        />
                    </svg>
                    <svg
                        v-else
                        xmlns="http://www.w3.org/2000/svg"
                        class="w-5 h-5 text-blue-500"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                    >
                        <path
                            d="M4 13v3h3l8-8-3-3-8 8zm10.707-9.707a1 1 0 00-1.414-1.414l-1.586 1.586 3 3 1.586-1.586z"
                        />
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="font-medium text-gray-800">
                        {{ item.description }}
                    </p>
                    <p class="text-xs text-gray-500">{{ item.relativeDate }}</p>
                </div>
            </li>
        </transition-group>

        <!-- Aucune donnée -->
        <p v-if="!items.length && !loading" class="text-gray-500">
            Aucune activité récente.
        </p>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";

const items = ref([]);
const loading = ref(true);
const error = ref(null);

// Fonction basique de “relative time”
function relativeTime(isoDate) {
    const delta = (Date.now() - new Date(isoDate)) / 1000;
    if (delta < 60) return "À l’instant";
    if (delta < 3600) return `${Math.floor(delta / 60)} min`;
    if (delta < 86400) return `${Math.floor(delta / 3600)} h`;
    return `${Math.floor(delta / 86400)} j`;
}

onMounted(async () => {
    try {
        const res = await fetch("/api/recent-activities", {
            headers: { Accept: "application/json" },
        });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = await res.json();

        items.value = data.map((a) => ({
            id: a.id,
            action: a.action, // Assure-toi que ton API renvoie le champ "action"
            description: a.description,
            relativeDate: relativeTime(a.createdAt),
        }));
    } catch (e) {
        console.error(e);
        error.value = "Impossible de charger les activités.";
    } finally {
        loading.value = false;
    }
});
</script>

<style>
/* Transition de la liste */
.list-enter-from,
.list-leave-to {
    opacity: 0;
    transform: translateY(-5px);
}
.list-enter-to,
.list-leave-from {
    opacity: 1;
    transform: translateY(0);
}
.list-enter-active,
.list-leave-active {
    transition: all 0.3s ease;
}
</style>
