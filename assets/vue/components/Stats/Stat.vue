<template>
    <section class="p-6 bg-base-200 h-full overflow-y-auto">
        <!-- En-tête de la page de statistiques -->
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-3xl font-semibold text-base-content">
                Statistiques de gestion
            </h2>
        </div>

        <!-- Grille responsive de cartes KPI -->
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <!-- Pour chaque KPI, on génère une carte -->
            <a
                v-for="card in kpis"
                :key="card.key"
                :href="routes[card.key]"
                class="card bg-base-100 shadow-lg hover:shadow-xl transition p-5 flex items-center space-x-4 hover:bg-base-200 transform hover:-translate-y-1 cursor-pointer"
            >
                <div class="flex items-center space-x-4">
                    <!-- Valeur chiffrée du KPI -->
                    <p class="text-3xl font-bold text-base-content">
                        {{ card.value }}
                    </p>
                    <!-- Icône associée au KPI -->
                    <component :is="card.icon" class="w-8 h-8 text-primary" />
                    <!-- Label descriptif en majuscules -->
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

// Définition réactive de la liste des KPI avec valeurs par défaut
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

// Mapping des clés KPI vers les routes cibles
const routes = {
    users: "/user",
    formations: "/formation#formations",
    sessions: "/formation#sessions",
};

/**
 * Récupère les données KPI depuis l'API et met à jour les valeurs.
 * En cas d'erreur de réponse, on ignore et on conserve les valeurs par défaut.
 */
async function loadKpis() {
    try {
        const res = await fetch("/api/stats/kpis");
        if (!res.ok) {
            console.warn("Échec du chargement des KPI:", res.status);
            return;
        }
        const data = await res.json();
        // Mise à jour des valeurs pour chaque carte
        kpis.forEach((card) => {
            card.value = data[card.key] ?? 0;
        });
    } catch (err) {
        console.error("Erreur lors de la requête KPI:", err);
    }
}

// Chargement initial des KPI au montage du composant
onMounted(loadKpis);
</script>

<style scoped>
/*
    Aucune règle CSS custom: utilisation de DaisyUI et Tailwind pour la mise en forme.
  */
</style>
