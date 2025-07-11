<template>
    <section class="p-6 bg-gray-50 h-full overflow-y-auto">
        <!-- Entête avec bouton “Nouvelle session” -->
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-semibold">Mes sessions</h2>
            <button
                @click="goToNew"
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700 transition"
            >
                <Plus class="w-5 h-5 mr-2" />
                Nouvelle session
            </button>
        </div>

        <!-- Recherche -->
        <div class="mb-4">
            <input
                v-model="search"
                type="text"
                placeholder="Rechercher une session..."
                class="w-full max-w-md px-3 py-2 border rounded focus:ring focus:border-blue-300"
            />
        </div>

        <!-- Liste des sessions -->
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            <div
                v-for="s in filtered"
                :key="s.id"
                class="bg-white p-4 rounded shadow hover:shadow-lg transition"
            >
                <h3 class="font-semibold text-lg mb-2">{{ s.titre }}</h3>
                <p class="text-sm text-gray-600">
                    Du {{ formatDate(s.dateDebut) }} au
                    {{ formatDate(s.dateFin) }}
                </p>
                <p class="text-sm text-gray-600">
                    Actif : {{ s.isActive ? "Oui" : "Non" }}
                </p>
                <div class="mt-4 flex justify-between">
                    <a
                        :href="showUrl(s.id)"
                        class="text-blue-600 hover:underline text-sm"
                        >Voir</a
                    >
                    <button
                        @click="edit(s.id)"
                        class="text-gray-500 hover:text-gray-700 text-sm"
                    >
                        Modifier
                    </button>
                </div>
            </div>
        </div>

        <!-- Message si aucune session -->
        <p v-if="!sessions.length" class="text-gray-500 mt-6">
            Aucune session trouvée.
        </p>
    </section>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { Plus } from "lucide-vue-next";

// 1. Props en haut
const props = defineProps({
    apiUrl: { type: String, required: true },
    newUrl: { type: String, required: true },
});

const sessions = ref([]);
const search = ref("");

// 2. Chargement + mapping pour unifier les clés
async function load() {
    const res = await fetch(props.apiUrl);
    if (!res.ok) return;
    const data = await res.json();
    sessions.value = data.map((s) => ({
        id: s.id,
        titre: s.titre, // correspond à ton API
        dateDebut: s.dateDebut,
        dateFin: s.dateFin,
        // normalisation du booléen
        isActive: s.isActive === true || s.isActive === 1 || s.isActive === "1",
    }));
}

// 3. Filtre sur le bon champ
const filtered = computed(() => {
    const term = search.value.toLowerCase();
    return sessions.value.filter((s) => s.titre.toLowerCase().includes(term));
});

// 4. Navigation et utilitaires
function goToNew() {
    window.location.href = props.newUrl;
}
function showUrl(id) {
    return `/session/${id}`;
}
function edit(id) {
    window.location.href = `/session/${id}/edit`;
}
function formatDate(iso) {
    if (!iso) return "";
    const d = new Date(iso);
    return d.toLocaleDateString("fr-FR", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
    });
}

onMounted(load);
</script>
