<template>
    <div class="card bg-base-100 shadow-xl p-6">
        <h2 class="text-xl font-semibold mb-4 text-base-content">
            Activités récentes
        </h2>

        <!-- Skeleton loader -->
        <div v-if="loading" class="space-y-2 animate-pulse">
            <div class="h-4 bg-base-200 rounded w-3/4"></div>
            <div class="h-4 bg-base-200 rounded w-2/3"></div>
            <div class="h-4 bg-base-200 rounded w-5/6"></div>
        </div>

        <!-- Message d'erreur -->
        <p v-else-if="error" class="text-error">{{ error }}</p>

        <!-- Liste animée -->
        <ul v-else-if="items.length" class="space-y-3">
            <transition-group name="list" tag="div">
                <li
                    v-for="item in items"
                    :key="item.id"
                    class="flex items-start gap-3 p-3 rounded-lg hover:bg-base-200 transition"
                >
                    <div class="p-2 bg-base-200 rounded-full flex-shrink-0">
                        <component
                            :is="getIconComponent(item.action)"
                            class="w-5 h-5"
                        />
                    </div>
                    <div class="flex-1">
                        <p class="font-medium text-base-content">
                            {{ item.description }}
                        </p>
                        <p class="text-xs text-base-content opacity-60">
                            {{ item.relativeDate }}
                        </p>
                    </div>
                </li>
            </transition-group>
        </ul>

        <!-- Aucune donnée -->
        <p v-else class="text-base-content opacity-60">
            Aucune activité récente.
        </p>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import {
    Trash2 as DeleteIcon,
    PlusCircle as CreateIcon,
    Edit2 as EditIcon,
} from "lucide-vue-next";

const items = ref([]);
const loading = ref(true);
const error = ref(null);

function relativeTime(isoDate) {
    const delta = (Date.now() - new Date(isoDate)) / 1000;
    if (delta < 60) return "À l’instant";
    if (delta < 3600) return `${Math.floor(delta / 60)} min`;
    if (delta < 86400) return `${Math.floor(delta / 3600)} h`;
    return `${Math.floor(delta / 86400)} j`;
}

function getIconComponent(action) {
    switch (action) {
        case "delete":
            return DeleteIcon;
        case "create":
            return CreateIcon;
        default:
            return EditIcon;
    }
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
            action: a.action,
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

<style scoped>
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
