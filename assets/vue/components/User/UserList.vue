<template>
    <section class="p-6 bg-gray-50 h-full overflow-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-semibold">Utilisateurs</h2>

            <div class="flex items-center space-x-2">
                <!-- Import dropdown -->
                <div class="relative inline-block text-left">
                    <button
                        @click="dropdownOpen = !dropdownOpen"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700 transition"
                    >
                        📥 Importer
                        <svg
                            class="ml-2 w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 9l-7 7-7-7"
                            />
                        </svg>
                    </button>
                    <div
                        v-if="dropdownOpen"
                        class="absolute right-0 mt-2 w-40 bg-white border border-gray-200 rounded shadow-lg z-50"
                    >
                        <ImportOption
                            label="📄 CSV"
                            accept=".csv"
                            @change="onCSVImport"
                            inputId="csv-upload"
                        />
                        <ImportOption
                            label="📇 vCard (.vcf)"
                            accept=".vcf"
                            @change="onVCFImport"
                            inputId="vcf-upload"
                        />
                    </div>
                </div>

                <!-- Ajouter -->
                <button
                    @click="goToNewUser"
                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded hover:bg-green-700 transition"
                >
                    <Plus class="w-5 h-5 mr-2" />
                    Ajouter un utilisateur
                </button>
            </div>
        </div>

        <!-- Search -->
        <div class="mb-4">
            <input
                v-model="searchTerm"
                type="text"
                placeholder="Rechercher un utilisateur..."
                class="w-full max-w-md px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring focus:border-blue-300"
            />
        </div>

        <!-- User cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div
                v-for="user in filteredUsers"
                :key="user.id"
                class="bg-white p-4 rounded shadow hover:shadow-lg transition relative"
            >
                <template v-if="editingId === user.id">
                    <!-- Mode édition rapide -->
                    <div class="space-y-2">
                        <input
                            v-model="user.prenom"
                            placeholder="Prénom"
                            class="w-full border rounded px-2 py-1"
                        />
                        <input
                            v-model="user.nom"
                            placeholder="Nom"
                            class="w-full border rounded px-2 py-1"
                        />
                        <input
                            v-model="user.email"
                            placeholder="Email"
                            class="w-full border rounded px-2 py-1"
                        />
                        <select
                            v-model="user.role"
                            class="w-full border rounded px-2 py-1"
                        >
                            <option
                                v-for="opt in roleOptions"
                                :key="opt.value"
                                :value="opt.value"
                            >
                                {{ opt.label }}
                            </option>
                        </select>
                        <label class="inline-flex items-center">
                            <input
                                type="checkbox"
                                v-model="user.isActive"
                                class="form-checkbox h-5 w-5 text-blue-600"
                            />
                            <span class="ml-2">Compte actif</span>
                        </label>
                    </div>
                    <div class="flex items-center space-x-2 mt-3">
                        <button
                            @click="saveUser(user)"
                            :disabled="saving"
                            class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700"
                        >
                            <span v-if="saving">Sauvegarde...</span>
                            <span v-else>Enregistrer</span>
                        </button>
                        <button
                            @click="cancelEdit"
                            class="px-3 py-1 bg-gray-300 text-gray-800 rounded hover:bg-gray-400"
                        >
                            Annuler
                        </button>
                    </div>
                    <div
                        v-if="successId === user.id"
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
                        {{ user.prenom }} {{ user.nom }}
                    </h3>
                    <p class="text-gray-600 text-sm mb-1">{{ user.email }}</p>
                    <p class="text-gray-600 text-sm mb-1">
                        Rôle:
                        {{
                            roleOptions.find((r) => r.value === user.role)
                                ?.label || user.role
                        }}
                    </p>
                    <p class="text-gray-600 text-sm">
                        Actif: {{ user.isActive ? "Oui" : "Non" }}
                    </p>
                    <div class="flex justify-between mt-4">
                        <a
                            :href="getShowUrl(user.id)"
                            class="text-blue-600 hover:underline text-sm"
                        >
                            Voir détails
                        </a>
                        <button
                            @click="editUser(user)"
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
import Papa from "papaparse";
import { Plus } from "lucide-vue-next";
import ImportOption from "./ImportOption.vue";

// Routes injectées via Twig ou fallback
const userNewUrl = window.APP_ROUTES?.userNew || "/user/new";
const userShowTemplate = window.APP_ROUTES?.userShow || "/user/ID_PLACEHOLDER";
const userApiList = window.APP_ROUTES?.userApiList || "/api/users";
const userApiDetail =
    window.APP_ROUTES?.userApiDetail || "/api/user/ID_PLACEHOLDER";
const importEndpoint = window.APP_ROUTES?.userImport || "/api/import/users";

const users = ref([]);
const searchTerm = ref("");
const editingId = ref(null);
const saving = ref(false);
const error = ref("");
const successId = ref(null);
const dropdownOpen = ref(false);

const filteredUsers = computed(() => {
    const term = searchTerm.value.toLowerCase();
    return users.value.filter(
        (u) =>
            u.prenom.toLowerCase().includes(term) ||
            u.nom.toLowerCase().includes(term) ||
            u.email.toLowerCase().includes(term)
    );
});

// 1) Mapping de l'Enum UserRole => Label humain
const roleOptions = [
    { value: "ADMIN_CENTRE", label: "Administrateur Centre" },
    { value: "FORMATEUR", label: "Formateur" },
    { value: "STAGIAIRE", label: "Stagiaire" },
    { value: "ASSISTANT", label: "Assistant" },
];

function getShowUrl(id) {
    return userShowTemplate.replace("ID_PLACEHOLDER", id);
}
function goToNewUser() {
    window.location.assign(userNewUrl);
}
function editUser(u) {
    editingId.value = u.id;
    error.value = "";
    successId.value = null;
}
function cancelEdit() {
    editingId.value = null;
    error.value = "";
    successId.value = null;
}

async function saveUser(u) {
    saving.value = true;
    error.value = "";
    try {
        const payload = {
            prenom: u.prenom,
            nom: u.nom,
            email: u.email,
            role: u.role,
            isActive: u.isActive,
        };
        const res = await fetch(userApiDetail.replace("ID_PLACEHOLDER", u.id), {
            method: "PATCH",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload),
        });
        if (!res.ok) throw new Error("Erreur lors de la sauvegarde");
        successId.value = u.id;
        editingId.value = null;
    } catch (err) {
        error.value = err.message;
    } finally {
        saving.value = false;
    }
}

function parseVCF(raw) {
    const contacts = [];
    raw.replace(/\r\n/g, "\n")
        .split(/BEGIN:VCARD/i)
        .slice(1)
        .forEach((card) => {
            let nom = "",
                prenom = "",
                email = "";
            card.split(/END:VCARD/i)[0]
                .split("\n")
                .forEach((line) => {
                    if (/^N[:;]/.test(line)) {
                        const [n, p] = line.replace(/^N[^:]*:/, "").split(";");
                        nom = decodeURIComponent(n.replace(/=/g, "%"));
                        prenom = decodeURIComponent(p.replace(/=/g, "%"));
                    } else if (/^EMAIL/.test(line)) {
                        email = line.split(":").pop().trim();
                    }
                });
            if (nom && prenom && email) {
                contacts.push({ nom, prenom, email });
            }
        });
    return contacts;
}

async function importUsers(payload) {
    try {
        const res = await fetch(importEndpoint, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload),
        });
        const { users: imported } = await res.json();
        imported.forEach((u) => {
            users.value.push({
                id: u.id,
                prenom: u.prenom,
                nom: u.nom,
                email: u.email,
                role: u.role,
                isActive: u.isActive,
            });
        });
    } catch (err) {
        console.error("Erreur import :", err);
    } finally {
        dropdownOpen.value = false;
    }
}

function onCSVImport(file) {
    if (!file) return;
    Papa.parse(file, {
        header: true,
        skipEmptyLines: true,
        complete: ({ data }) => {
            const clean = data.filter((r) => r.nom && r.prenom && r.email);
            importUsers(clean);
        },
    });
}

function onVCFImport(file) {
    if (!file) return;
    const reader = new FileReader();
    reader.onload = (e) => importUsers(parseVCF(e.target.result));
    reader.readAsText(file);
}

onMounted(async () => {
    try {
        const res = await fetch(userApiList);
        const data = await res.json();
        users.value = data.map((u) => ({
            id: u.id,
            prenom: u.prenom,
            nom: u.nom,
            email: u.email,
            role: u.role,
            isActive: u.isActive,
        }));
    } catch (err) {
        console.error("Erreur chargement utilisateurs", err);
    }
});
</script>
