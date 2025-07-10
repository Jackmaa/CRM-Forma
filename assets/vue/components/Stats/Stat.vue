<template>
    <section class="p-6 bg-gray-50 h-full overflow-y-auto overflow-x-hidden">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-3xl font-semibold">Statistiques de gestion</h2>
        </div>

        <!-- KPI Cards -->
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <div
                v-for="card in kpis"
                :key="card.label"
                class="bg-white p-5 rounded-lg shadow hover:shadow-lg transition flex items-center space-x-4"
            >
                <component :is="card.icon" class="w-8 h-8 text-indigo-500" />
                <div>
                    <p class="text-xs text-gray-400 uppercase">
                        {{ card.label }}
                    </p>
                    <p class="text-3xl font-bold">{{ card.value }}</p>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { reactive, onMounted } from "vue";
import {
    UsersIcon as Users,
    BookOpenIcon as BookOpen,
    ActivityIcon as Activity,
} from "lucide-vue-next";

const kpis = reactive([
    { label: "Utilisateurs", key: "users", icon: Users, value: "…" },
    { label: "Formations", key: "formations", icon: BookOpen, value: "…" },
    { label: "Sessions actives", key: "sessions", icon: Activity, value: "…" },
]);

async function loadKpis() {
    const res = await fetch("/api/stats/kpis");
    if (!res.ok) return;
    const data = await res.json();
    kpis.forEach((card) => {
        card.value = data[card.key] ?? 0;
    });
}

onMounted(loadKpis);
</script>
