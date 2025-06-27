<template>
    <section class="p-6 bg-gray-50 h-full overflow-auto">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-semibold">Formations</h2>
            <button
                @click="goToNewFormation"
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700 transition"
            >
                <Plus class="w-5 h-5 mr-2" />
                Ajouter une formation
            </button>
        </div>

        <div class="mb-4">
            <input
                v-model="searchTerm"
                type="text"
                placeholder="Rechercher une formation..."
                class="w-full max-w-md px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring focus:border-blue-300"
            />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div
                v-for="formation in filteredFormations"
                :key="formation.id"
                class="bg-white p-4 rounded shadow hover:shadow-lg transition"
            >
                <h3 class="text-lg font-semibold mb-2">
                    {{ formation.title }}
                </h3>
                <p class="text-gray-600 text-sm mb-4">
                    {{ formation.description }}
                </p>
                <div class="flex justify-end">
                    <button
                        @click="viewFormation(formation.id)"
                        class="text-blue-600 hover:underline text-sm"
                    >
                        Voir détails
                    </button>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { Plus } from "lucide-vue-next";
const newUrl = window.APP_ROUTES?.formationNew || "/formation/new";
// Données simulées ou chargées via API
const formations = ref([]);
const searchTerm = ref("");

const filteredFormations = computed(() => {
    const term = searchTerm.value.toLowerCase();
    return formations.value.filter(
        (f) =>
            f.title.toLowerCase().includes(term) ||
            f.description.toLowerCase().includes(term)
    );
});
function goToNewFormation() {
    // Symfony génère le chemin, on l'injecte ici :
    window.location.assign(newUrl);
}
function viewFormation(id) {
    // Naviguer vers la page de détail, e.g. via vue-router
    // router.push({ name: 'FormationDetail', params: { id } })
}

// Simuler un chargement depuis l'API
onMounted(async () => {
    formations.value = [
        {
            id: 1,
            title: "Vue.js Avancé",
            description: "Approfondissez vos connaissances en Vue 3.",
        },
        {
            id: 2,
            title: "Tailwind CSS pour Pros",
            description: "Maîtrisez Tailwind pour des interfaces rapides.",
        },
        {
            id: 3,
            title: "Node.js et Express",
            description: "Créez des API robustes avec Node et Express.",
        },
    ];

    const data = await fetch("/formation/api").then((res) => res.json());
    formations.value.push(...data);
});
</script>
