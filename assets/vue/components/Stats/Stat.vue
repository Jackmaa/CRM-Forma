<template>
    <section class="p-6 bg-base-200 h-full overflow-y-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-3xl font-semibold text-base-content">
                Statistiques de gestion
            </h2>
        </div>

        <!-- KPI Cards DaisyUI -->
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <a
                v-for="card in kpis"
                :key="card.label"
                :href="routes[card.key]"
                class="card bg-base-100 shadow-lg hover:shadow-xl transition p-5 flex items-center space-x-4 hover:bg-base-200 transform hover:-translate-y-1 cursor-pointer"
            >
                <div
                    class="flex flex-row items-center justify-center space-x-4"
                >
                    <p class="text-3xl font-bold text-base-content">
                        {{ card.value }}
                    </p>
                    <component :is="card.icon" class="w-8 h-8 text-primary" />
                    <p class="text-xs uppercase text-base-content opacity-60">
                        {{ card.label }}
                    </p>
                </div>
            </a>
        </div>
    </section>
</template>

<script setup>
import { reactive, onMounted } from "vue";
import {
    Users as UsersIcon,
    BookOpen as BookOpenIcon,
    Activity as ActivityIcon,
} from "lucide-vue-next";

// données des KPI
const kpis = reactive([
    { label: "Utilisateurs", key: "users", icon: UsersIcon, value: "…" },
    { label: "Formations", key: "formations", icon: BookOpenIcon, value: "…" },
    {
        label: "Sessions actives",
        key: "sessions",
        icon: ActivityIcon,
        value: "…",
    },
]);

// mapping clé → URL cible
const routes = {
    users: "/user",
    formations: "/formation#formations",
    sessions: "/formation#sessions",
};

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

<style scoped>
/* Pas de CSS custom ; DaisyUI + Tailwind gèrent tout */
</style>
