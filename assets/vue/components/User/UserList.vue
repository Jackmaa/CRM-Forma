<template>
    <section class="p-6 bg-base-200 h-full overflow-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-semibold text-base-content">
                Utilisateurs
            </h2>
            <div class="flex items-center space-x-2">
                <!-- Import dropdown : visible uniquement aux admins -->
                <div v-if="isAdmin" class="dropdown">
                    <label tabindex="0" class="btn btn-primary btn-sm gap-2">
                        📥 Importer
                        <svg
                            class="w-4 h-4 ml-1"
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
                    </label>
                    <ul
                        tabindex="0"
                        class="dropdown-content menu p-2 shadow bg-base-100 rounded-box w-40"
                    >
                        <li>
                            <ImportOption
                                label="📄 CSV"
                                accept=".csv"
                                @change="onCSVImport"
                                inputId="csv-upload"
                            />
                        </li>
                        <li>
                            <ImportOption
                                label="📇 vCard (.vcf)"
                                accept=".vcf"
                                @change="onVCFImport"
                                inputId="vcf-upload"
                            />
                        </li>
                    </ul>
                </div>
                <!-- Ajouter : visible uniquement aux admins -->
                <button
                    v-if="isAdmin"
                    @click="goToNewUser"
                    class="btn btn-success btn-sm flex items-center gap-2"
                >
                    <Plus class="w-5 h-5" /> Ajouter
                </button>
            </div>
        </div>

        <!-- Search & Filter -->
        <div class="flex flex-col md:flex-row gap-4 mb-6">
            <input
                v-model="searchTerm"
                type="text"
                placeholder="Rechercher..."
                class="input input-bordered w-full md:w-1/2"
            />
            <select
                v-model="roleFilter"
                class="select select-bordered w-full md:w-1/4"
            >
                <option value="">Tous rôles</option>
                <option
                    v-for="opt in roleOptions"
                    :key="opt.value"
                    :value="opt.value"
                >
                    {{ opt.label }}
                </option>
            </select>
        </div>

        <!-- User cards -->
        <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            <div
                v-for="user in filteredUsers"
                :key="user.id"
                class="card bg-base-100 shadow hover:shadow-lg transition p-4 relative"
            >
                <template v-if="editingId === user.id">
                    <!-- édition : visible uniquement aux admins -->
                    <div v-if="isAdmin" class="space-y-2">
                        <!-- champs éditables… -->
                    </div>
                    <div v-if="isAdmin" class="flex justify-end space-x-2 mt-4">
                        <button
                            @click="saveUser(user)"
                            :disabled="saving"
                            class="btn btn-success btn-sm"
                        >
                            {{ saving ? "Sauvegarde..." : "Enregistrer" }}
                        </button>
                        <button
                            @click="cancelEdit"
                            class="btn btn-ghost btn-sm"
                        >
                            Annuler
                        </button>
                    </div>
                </template>

                <template v-else>
                    <!-- vue simple -->
                    <h3 class="text-lg font-semibold text-base-content mb-1">
                        {{ user.prenom }} {{ user.nom }}
                    </h3>
                    <p class="text-sm text-base-content opacity-70">
                        {{ user.email }}
                    </p>
                    <p class="text-sm text-base-content opacity-70">
                        Rôle:
                        {{
                            roleOptions.find((r) => r.value === user.role)
                                ?.label || user.role
                        }}
                    </p>
                    <p class="text-sm text-base-content opacity-70">
                        Actif: {{ user.isActive ? "Oui" : "Non" }}
                    </p>
                    <div class="flex justify-between mt-4">
                        <a
                            :href="getShowUrl(user.id)"
                            class="link link-primary text-sm"
                            >Voir détails</a
                        >
                        <!-- Modifier : visible uniquement aux admins -->
                        <button
                            v-if="isAdmin"
                            @click="editUser(user)"
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
 * Composant d'affichage de la liste des utilisateurs.
 *
 * Affiche les utilisateurs sous forme de cartes, permet la recherche, le filtrage, l'import (admin), l'édition rapide (admin), et la navigation vers le détail.
 *
 * État local :
 * - users : liste des utilisateurs
 * - searchTerm : terme de recherche
 * - roleFilter : filtre par rôle
 * - editingId : id de l'utilisateur en édition
 * - saving : état de sauvegarde
 * - error : message d'erreur
 * - successId : id de l'utilisateur sauvegardé avec succès
 */
import { ref, computed, onMounted } from "vue";
import Papa from "papaparse";
import { Plus } from "lucide-vue-next";
import ImportOption from "./ImportOption.vue";
import { useAuth } from "@/composables/useAuth";

const { isAdmin } = useAuth();

const userNewUrl = window.APP_ROUTES?.userNew || "/user/new";
const userShowTemplate = window.APP_ROUTES?.userShow || "/user/ID_PLACEHOLDER";
const userApiList = window.APP_ROUTES?.userApiList || "/api/users";
const userApiDetail =
    window.APP_ROUTES?.userApiDetail || "/api/user/ID_PLACEHOLDER";
const importEndpoint = window.APP_ROUTES?.userImport || "/api/import/users";

const users = ref([]);
const searchTerm = ref("");
const roleFilter = ref("");
const editingId = ref(null);
const saving = ref(false);
const error = ref("");
const successId = ref(null);

const filteredUsers = computed(() => {
    const term = searchTerm.value.toLowerCase();
    return users.value.filter((u) => {
        const matchSearch = [u.prenom, u.nom, u.email].some((f) =>
            f.toLowerCase().includes(term)
        );
        const matchRole = !roleFilter.value || u.role === roleFilter.value;
        return matchSearch && matchRole;
    });
});

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
    window.location.href = userNewUrl;
}
function editUser(u) {
    editingId.value = u.id;
    error.value = "";
    successId.value = null;
}
function cancelEdit() {
    editingId.value = null;
    error.value = "";
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
    } catch (e) {
        error.value = e.message;
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
            if (nom && prenom && email) contacts.push({ nom, prenom, email });
        });
    return contacts;
}

async function importUsers(payload) {
    try {
        const res = await fetch(importEndpoint, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ users: payload }),
        });
        const { users: imported } = await res.json();
        imported.forEach((u) =>
            users.value.push({
                id: u.id,
                prenom: u.prenom,
                nom: u.nom,
                email: u.email,
                role: u.role,
                isActive: u.isActive,
            })
        );
    } catch (e) {
        console.error("Erreur import :", e);
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
    } catch (e) {
        console.error("Erreur chargement utilisateurs", e);
    }
});
</script>

<style scoped>
/* DaisyUI gère les styles, pas de CSS custom nécessaire */
</style>
