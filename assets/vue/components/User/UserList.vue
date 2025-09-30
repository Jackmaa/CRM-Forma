<template>
    <section class="p-6 bg-base-200 h-full overflow-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-semibold text-base-content">
                Utilisateurs
            </h2>

            <div class="flex items-center space-x-2">
                <!-- Import dropdown : admins only -->
                <div v-if="isAdmin" class="dropdown dropdown-end">
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
                        class="dropdown-content menu p-2 shadow bg-base-100 rounded-box w-44"
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

                <!-- Ajouter : admins only -->
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
                v-model.trim="searchTerm"
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
                <!-- MODE ÉDITION (admins only) -->
                <template v-if="editingId === user.id && isAdmin">
                    <div class="space-y-2">
                        <!-- Prénom -->
                        <div class="form-control">
                            <label class="label"
                                ><span class="label-text">Prénom</span></label
                            >
                            <input
                                v-model.trim="editBuffer.prenom"
                                type="text"
                                class="input input-bordered w-full"
                            />
                        </div>

                        <!-- Nom -->
                        <div class="form-control">
                            <label class="label"
                                ><span class="label-text">Nom</span></label
                            >
                            <input
                                v-model.trim="editBuffer.nom"
                                type="text"
                                class="input input-bordered w-full"
                            />
                        </div>

                        <!-- Email -->
                        <div class="form-control">
                            <label class="label"
                                ><span class="label-text">Email</span></label
                            >
                            <input
                                v-model.trim="editBuffer.email"
                                type="email"
                                class="input input-bordered w-full"
                            />
                        </div>

                        <!-- Rôle -->
                        <div class="form-control">
                            <label class="label"
                                ><span class="label-text">Rôle</span></label
                            >
                            <select
                                v-model="editBuffer.role"
                                class="select select-bordered w-full"
                            >
                                <option
                                    v-for="opt in roleOptions"
                                    :key="opt.value"
                                    :value="opt.value"
                                >
                                    {{ opt.label }}
                                </option>
                            </select>
                        </div>

                        <!-- Actif -->
                        <div class="form-control">
                            <label class="label cursor-pointer">
                                <input
                                    type="checkbox"
                                    v-model="editBuffer.isActive"
                                    class="checkbox checkbox-primary"
                                />
                                <span class="label-text ml-2">Actif</span>
                            </label>
                        </div>

                        <!-- erreurs -->
                        <p v-if="error" class="text-error text-sm mt-2">
                            {{ error }}
                        </p>
                        <p
                            v-if="successId === user.id"
                            class="text-success text-sm mt-1"
                        >
                            Enregistré ✅
                        </p>
                    </div>

                    <!-- boutons Enregistrer / Annuler -->
                    <div class="flex justify-end space-x-2 mt-4">
                        <button
                            @click="saveUser(user.id)"
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

                <!-- MODE LECTURE -->
                <template v-else>
                    <h3 class="text-lg font-semibold text-base-content mb-1">
                        {{ user.fullname || `${user.prenom} ${user.nom}` }}
                    </h3>
                    <p class="text-sm text-base-content/70">{{ user.email }}</p>
                    <p class="text-sm text-base-content/70">
                        Rôle:
                        {{
                            roleOptions.find((r) => r.value === user.role)
                                ?.label || user.role
                        }}
                    </p>
                    <p class="text-sm text-base-content/70">
                        Actif: {{ user.isActive ? "Oui" : "Non" }}
                    </p>
                    <div class="flex justify-between mt-4">
                        <a
                            :href="getShowUrl(user.id)"
                            class="link link-primary text-sm"
                            >Voir détails</a
                        >
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
 * Liste des utilisateurs (cards) + recherche/filtre + import CSV/VCF + édition rapide (admins).
 * Corrigé pour:
 * - Utiliser apiFetch (JWT) partout (GET, PATCH, POST)
 * - Fallbacks d’URL en /api/...
 * - Buffer d’édition (évite de muter la liste tant que pas sauvegardé)
 * - Filtre/search robustes
 */
import { ref, computed, onMounted } from "vue";
import Papa from "papaparse";
import { Plus } from "lucide-vue-next";
import ImportOption from "./ImportOption.vue";
import { useAuth } from "@/composables/useAuth";
import { apiFetch } from "@/utils/apiFetch";
import { patchJson } from "@/utils/apiFetch";
import { postJson } from "@/utils/apiFetch";

const { isAdmin } = useAuth();

// Routes (fallbacks en /api/...)
const userNewUrl = window.APP_ROUTES?.userNew || "/user/new";
const userShowTemplate = window.APP_ROUTES?.userShow || "/user/ID_PLACEHOLDER";
const userApiList = window.APP_ROUTES?.userApiList || "/users";
const userApiDetailTpl =
    window.APP_ROUTES?.userApiDetail || "/user/ID_PLACEHOLDER";
const importEndpoint = window.APP_ROUTES?.userImport || "/import/users";

const users = ref([]);
const searchTerm = ref("");
const roleFilter = ref("");
const editingId = ref(null);
const editBuffer = ref(null);
const saving = ref(false);
const error = ref("");
const successId = ref(null);

const roleOptions = [
    { value: "ADMIN_CENTRE", label: "Administrateur Centre" },
    { value: "FORMATEUR", label: "Formateur" },
    { value: "STAGIAIRE", label: "Stagiaire" },
    { value: "ASSISTANT", label: "Assistant" },
];

const normalizeStr = (v) => (typeof v === "string" ? v : v ?? "");
const filteredUsers = computed(() => {
    const term = normalizeStr(searchTerm.value).toLowerCase();
    return users.value.filter((u) => {
        const fields = [u.prenom, u.nom, u.email].map(normalizeStr);
        const matchSearch =
            !term || fields.some((f) => f.toLowerCase().includes(term));
        const matchRole = !roleFilter.value || u.role === roleFilter.value;
        return matchSearch && matchRole;
    });
});

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
    // clone propre pour éviter de muter la liste avant save
    editBuffer.value = {
        prenom: u.prenom ?? "",
        nom: u.nom ?? "",
        email: u.email ?? "",
        role: u.role ?? "",
        isActive: !!u.isActive,
    };
}

function cancelEdit() {
    editingId.value = null;
    editBuffer.value = null;
    error.value = "";
    successId.value = null;
}

async function saveUser(userId) {
    if (!isAdmin) return;
    saving.value = true;
    error.value = "";
    try {
        const payload = { ...editBuffer.value };
        const url = userApiDetailTpl.replace("ID_PLACEHOLDER", userId);

        // ⬇️ data = ce que renvoie ton API (ou undefined si 204)
        const data = await patchJson(url, payload);
        const updated = data ?? payload; // fallback si 204 No Content

        const idx = users.value.findIndex((u) => u.id === userId);
        if (idx !== -1) users.value[idx] = { ...users.value[idx], ...updated };

        successId.value = userId;
        editingId.value = null;
        editBuffer.value = null;
    } catch (e) {
        error.value = e.message || "Erreur inconnue";
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
                        // vCard quoted-printable → simple decode best-effort
                        nom = decodeURIComponent(n.replace(/=/g, "%"));
                        prenom = decodeURIComponent(
                            p?.replace(/=/g, "%") || ""
                        );
                    } else if (/^EMAIL/i.test(line)) {
                        email = line.split(":").pop().trim();
                    }
                });
            if (nom && prenom && email) contacts.push({ nom, prenom, email });
        });
    return contacts;
}

async function importUsers(payload) {
    if (!isAdmin || !Array.isArray(payload) || !payload.length) return;
    try {
        // Le contrôleur peut renvoyer { users: [...] } ou directement un tableau
        const result = await postJson(importEndpoint, { users: payload });
        const imported = Array.isArray(result) ? result : result?.users || [];

        imported.forEach((u) => {
            users.value.push({
                id: u.id,
                prenom: u.prenom,
                nom: u.nom,
                email: u.email,
                role: u.role,
                isActive: !!u.isActive,
            });
        });
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
            const clean = data
                .map((r) => ({
                    nom: normalizeStr(r.nom).trim(),
                    prenom: normalizeStr(r.prenom).trim(),
                    email: normalizeStr(r.email).trim(),
                    role: normalizeStr(r.role).trim() || "STAGIAIRE",
                    isActive: String(r.isActive).toLowerCase() === "true",
                }))
                .filter((r) => r.nom && r.prenom && r.email);
            importUsers(clean);
        },
    });
}

function onVCFImport(file) {
    if (!file) return;
    const reader = new FileReader();
    reader.onload = (e) => {
        const contacts = parseVCF(e.target.result);
        // rôle par défaut & actif
        const payload = contacts.map((c) => ({
            ...c,
            role: "STAGIAIRE",
            isActive: true,
        }));
        importUsers(payload);
    };
    reader.readAsText(file);
}
onMounted(async () => {
    try {
        // data est directement le tableau renvoyé par le contrôleur Symfony
        const data = await apiFetch(userApiList);

        users.value = (Array.isArray(data) ? data : data?.users || []).map(
            (u) => ({
                id: u.id,
                prenom: u.prenom,
                nom: u.nom,
                email: u.email,
                role: u.role,
                isActive: !!u.isActive,
                fullname: u.fullname,
                initials: u.initials,
            })
        );
    } catch (e) {
        console.error("Erreur chargement utilisateurs", e);
        error.value = e?.message || "Chargement utilisateurs échoué";
    }
});
</script>

<style scoped>
/* DaisyUI gère les styles */
</style>
