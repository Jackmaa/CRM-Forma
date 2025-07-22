<template>
    <section class="p-6 bg-base-200 h-full overflow-y-auto">
        <!-- Entête avec bouton “Nouvelle session” -->
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-semibold text-base-content">
                Mes sessions
            </h2>
            <button
                v-if="isAdmin"
                @click="goToNew"
                class="btn btn-primary btn-sm flex items-center"
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
                class="input input-bordered w-full max-w-md"
            />
        </div>

        <!-- Liste des sessions -->
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            <div
                v-for="s in filtered"
                :key="s.id"
                class="card bg-base-100 shadow hover:shadow-lg transition p-4"
            >
                <h3 class="font-semibold text-lg text-base-content mb-2">
                    {{ s.titre }}
                </h3>
                <p class="text-sm text-base-content opacity-70">
                    Du {{ formatDate(s.dateDebut) }} au
                    {{ formatDate(s.dateFin) }}
                </p>
                <p class="text-sm text-base-content opacity-70">
                    Actif : {{ s.isActive ? "Oui" : "Non" }}
                </p>
                <div class="mt-4 flex justify-between">
                    <a :href="showUrl(s.id)" class="link link-primary text-sm"
                        >Voir</a
                    >
                    <button
                        v-if="isAdmin"
                        @click="edit(s.id)"
                        class="btn btn-ghost btn-sm"
                    >
                        Modifier
                    </button>
                </div>
            </div>
        </div>

        <!-- Message si aucune session -->
        <p v-if="!sessions.length" class="text-base-content opacity-60 mt-6">
            Aucune session trouvée.
        </p>
    </section>
</template>

<script setup>
/**
 * Composant d'affichage de la liste des sessions.
 *
 * Affiche les sessions sous forme de cartes, permet la recherche, la navigation vers le détail et l'édition (admin).
 *
 * Props :
 * - apiUrl (String, requis) : URL d'API pour charger les sessions.
 * - newUrl (String, requis) : URL pour créer une nouvelle session.
 *
 * État local :
 * - sessions : liste des sessions
 * - search : terme de recherche
 */
import { ref, computed, onMounted } from "vue";
import { Plus } from "lucide-vue-next";
import { useAuth } from "@/composables/useAuth";

const { isAdmin } = useAuth();

const props = defineProps({
    apiUrl: { type: String, required: true },
    newUrl: { type: String, required: true },
});

const sessions = ref([]);
const search = ref("");

/**
 * Charge les sessions depuis l'API.
 */
async function load() {
    const res = await fetch(props.apiUrl);
    if (!res.ok) return;
    const data = await res.json();
    sessions.value = data.map((s) => ({
        id: s.id,
        titre: s.titre,
        dateDebut: s.dateDebut,
        dateFin: s.dateFin,
        isActive: s.isActive === true || s.isActive === 1 || s.isActive === "1",
    }));
}

/**
 * Liste filtrée selon le terme de recherche.
 */
const filtered = computed(() => {
    const term = search.value.toLowerCase();
    return sessions.value.filter((s) => s.titre.toLowerCase().includes(term));
});

/**
 * Redirige vers la page de création de session.
 */
function goToNew() {
    window.location.href = props.newUrl;
}

/**
 * Retourne l'URL de détail pour une session donnée.
 * @param {number|string} id
 * @returns {string}
 */
function showUrl(id) {
    return `/session/${id}`;
}

/**
 * Redirige vers la page d'édition d'une session.
 * @param {number|string} id
 */
function edit(id) {
    window.location.href = `/session/${id}/edit`;
}

/**
 * Formate une date ISO en chaîne lisible (fr-FR).
 * @param {string} iso
 * @returns {string}
 */
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

<style scoped>
/* DaisyUI gère les styles, pas de CSS custom ici */
</style>
