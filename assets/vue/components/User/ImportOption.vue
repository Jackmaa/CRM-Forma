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
            <div
                v-for="card in kpis"
                :key="card.label"
                class="card bg-base-100 shadow-lg hover:shadow-xl transition p-5 flex items-center space-x-4"
            >
                <component :is="card.icon" class="w-8 h-8 text-primary" />
                <div>
                    <p class="text-xs uppercase text-base-content opacity-60">
                        {{ card.label }}
                    </p>
                    <p class="text-3xl font-bold text-base-content">
                        {{ card.value }}
                    </p>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
/**
 * Composant d'option d'import pour les utilisateurs (CSV, vCard, etc.).
 *
 * Affiche un bouton d'import avec label et gère l'événement de changement de fichier.
 *
 * Props :
 * - label (String) : Libellé du bouton.
 * - accept (String) : Types de fichiers acceptés.
 * - inputId (String) : ID de l'input file.
 *
 * Événements :
 * - change : émis lors de la sélection d'un fichier.
 */
import { reactive, onMounted } from "vue";
import {
    Users as UsersIcon,
    BookOpen as BookOpenIcon,
    Activity as ActivityIcon,
} from "lucide-vue-next";

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
/* DaisyUI gère le style, pas de CSS custom nécessaire */
</style>
