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
                class="bg-white p-4 rounded shadow hover:shadow-lg transition relative"
            >
                <template v-if="editingId === formation.id">
                    <!-- Mode édition rapide -->
                    <input
                        v-model="formation.titre"
                        class="w-full border rounded px-2 py-1 mb-2"
                    />
                    <textarea
                        v-model="formation.description"
                        rows="3"
                        class="w-full border rounded px-2 py-1 mb-2"
                    ></textarea>
                    <div class="flex items-center space-x-2">
                        <button
                            @click="saveFormation(formation)"
                            :disabled="saving"
                            class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700"
                        >
                            <span v-if="saving">Sauvegarde...</span>
                            <span v-else>Enregistrer</span>
                        </button>
                        <button
                            @click="cancelEdit"
                            class="px-3 py-1 bg-gray-300 text-gray-800 rounded"
                        >
                            Annuler
                        </button>
                    </div>
                    <div
                        v-if="successId === formation.id"
                        class="text-green-600 mt-1"
                    >
                        Sauvegardé !
                    </div>
                    <div v-if="error" class="text-red-600 mt-1">
                        {{ error }}
                    </div>
                </template>
                <template v-else>
                    <!-- Mode affichage -->
                    <h3 class="text-lg font-semibold mb-2">
                        {{ formation.titre }}
                    </h3>
                    <p class="text-gray-600 text-sm mb-4">
                        {{ formation.description }}
                    </p>
                    <div class="flex justify-between">
                        <a
                            :href="getShowUrl(formation.id)"
                            class="text-blue-600 hover:underline text-sm"
                        >
                            Voir détails
                        </a>
                        <button
                            @click="editFormation(formation)"
                            class="text-gray-500 hover:text-gray-700 text-sm"
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
import { ref, computed, onMounted } from "vue";
import { Plus } from "lucide-vue-next";

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

const filteredFormations = computed(() => {
    const term = searchTerm.value.toLowerCase();
    return formations.value.filter(
        (f) =>
            f.titre.toLowerCase().includes(term) ||
            f.description.toLowerCase().includes(term)
    );
});

function getShowUrl(id) {
    return formationShowTemplate.replace("ID_PLACEHOLDER", id);
}

function goToNewFormation() {
    window.location.assign(props.newUrl);
}

function editFormation(f) {
    editingId.value = f.id;
    error.value = "";
    successId.value = null;
}

function cancelEdit() {
    editingId.value = null;
    error.value = "";
}

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

onMounted(async () => {
    const data = await fetch(props.apiUrl).then((res) => res.json());
    formations.value = data.map((f) => ({
        id: f.id,
        titre: f.title,
        description: f.description,
    }));
});
</script>
