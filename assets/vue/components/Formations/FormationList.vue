<template>
    <section class="p-6 bg-base-200 h-full overflow-auto">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-semibold text-base-content">Formations</h2>
            <button
                v-if="isAdmin"
                @click="goToNewFormation"
                class="btn btn-primary btn-sm flex items-center"
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
                class="input input-bordered w-full max-w-md"
            />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div
                v-for="formation in filteredFormations"
                :key="formation.id"
                class="card bg-base-100 shadow-lg hover:shadow-xl transition relative p-4"
            >
                <template v-if="editingId === formation.id">
                    <input
                        v-model="formation.titre"
                        class="input input-bordered w-full mb-2"
                    />
                    <textarea
                        v-model="formation.description"
                        rows="3"
                        class="textarea textarea-bordered w-full mb-2"
                    ></textarea>
                    <div class="flex items-center space-x-2">
                        <button
                            @click="saveFormation(formation)"
                            :disabled="saving"
                            class="btn btn-success btn-sm"
                        >
                            <span v-if="saving">Sauvegarde...</span>
                            <span v-else>Enregistrer</span>
                        </button>
                        <button
                            @click="cancelEdit"
                            class="btn btn-outline btn-sm"
                        >
                            Annuler
                        </button>
                    </div>
                    <p
                        v-if="successId === formation.id"
                        class="text-success mt-1"
                    >
                        Sauvegardé !
                    </p>
                    <p v-if="error" class="text-error mt-1">
                        {{ error }}
                    </p>
                </template>

                <template v-else>
                    <h3 class="text-lg font-semibold text-base-content mb-2">
                        {{ formation.titre }}
                    </h3>
                    <p class="text-base-content opacity-70 text-sm mb-4">
                        {{ formation.description }}
                    </p>
                    <div class="flex justify-between">
                        <a
                            :href="getShowUrl(formation.id)"
                            class="link link-primary text-sm"
                        >
                            Voir détails
                        </a>
                        <button
                            v-if="isAdmin"
                            @click="editFormation(formation)"
                            class="btn btn-ghost btn-sm"
                        >
                            Modifier
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </section>
</template>

<script setup>
/**
 * Composant d'affichage de la liste des formations.
 *
 * Affiche les formations sous forme de cartes, permet la recherche, l'édition rapide (admin), et la navigation vers le détail.
 *
 * Props :
 * - apiUrl (String, requis) : URL d'API pour charger les formations.
 * - newUrl (String, requis) : URL pour créer une nouvelle formation.
 *
 * État local :
 * - formations : liste des formations
 * - searchTerm : terme de recherche
 * - editingId : id de la formation en édition
 * - saving : état de sauvegarde
 * - error : message d'erreur
 * - successId : id de la formation sauvegardée avec succès
 */
import { ref, computed, onMounted } from "vue";
import { Plus } from "lucide-vue-next";
import { useAuth } from "@/composables/useAuth";

const { isStagiaire, roles, isAdmin } = useAuth();

const props = defineProps({
    apiUrl: { type: String, required: true },
    newUrl: { type: String, required: true },
});

const formationShowTemplate =
    window.APP_ROUTES?.formationShow || "/formation/ID_PLACEHOLDER";
const formationSaveUrl =
    window.APP_ROUTES?.formationSave || "/formation/ID_PLACEHOLDER/edit";

const formations = ref([]);
const searchTerm = ref("");
const editingId = ref(null);
const saving = ref(false);
const error = ref("");
const successId = ref(null);

/**
 * Liste filtrée selon le terme de recherche.
 */
const filteredFormations = computed(() => {
    const term = searchTerm.value.toLowerCase();
    return formations.value.filter(
        (f) =>
            f.titre.toLowerCase().includes(term) ||
            f.description.toLowerCase().includes(term)
    );
});

/**
 * Retourne l'URL de détail pour une formation donnée.
 * @param {number|string} id
 * @returns {string}
 */
function getShowUrl(id) {
    return formationShowTemplate.replace("ID_PLACEHOLDER", id);
}

/**
 * Redirige vers la page de création de formation.
 */
function goToNewFormation() {
    window.location.assign(props.newUrl);
}

/**
 * Passe une formation en mode édition.
 * @param {Object} f
 */
function editFormation(f) {
    editingId.value = f.id;
    error.value = "";
    successId.value = null;
}

/**
 * Annule l'édition en cours.
 */
function cancelEdit() {
    editingId.value = null;
    error.value = "";
}

/**
 * Sauvegarde les modifications d'une formation via l'API.
 * @param {Object} f
 */
async function saveFormation(f) {
    saving.value = true;
    error.value = "";
    successId.value = null;
    try {
        const res = await fetch(
            formationSaveUrl.replace("ID_PLACEHOLDER", f.id),
            {
                method: "PATCH",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    titre: f.titre,
                    description: f.description,
                }),
            }
        );
        if (!res.ok) throw new Error("Erreur lors de la sauvegarde");
        successId.value = f.id;
        editingId.value = null;
    } catch (err) {
        error.value = err.message;
    } finally {
        saving.value = false;
    }
}

// Chargement initial des formations à l'affichage du composant
onMounted(async () => {
    const data = await fetch(props.apiUrl).then((res) => res.json());
    formations.value = data.map((f) => ({
        id: f.id,
        titre: f.title,
        description: f.description,
    }));
});
console.log("Is stagiaire:", isStagiaire.value);
</script>

<style scoped>
/* Pas de CSS custom, DaisyUI gère le style et la responsivité */
</style>
