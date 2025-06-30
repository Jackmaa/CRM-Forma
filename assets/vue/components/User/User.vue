<template>
    <section class="p-6 bg-gray-50 h-full overflow-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-semibold">Utilisateurs</h2>
            <div class="relative inline-block text-left">
                <button
                    @click="dropdownOpen = !dropdownOpen"
                    class="inline-flex justify-center items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700 transition"
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

                <!-- Dropdown -->
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
        </div>

        <!-- Search -->
        <div class="mb-4">
            <input
                v-model="search"
                type="text"
                placeholder="Rechercher un utilisateur..."
                class="w-full max-w-md px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring focus:border-blue-300"
            />
        </div>

        <!-- User cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <UserCard
                v-for="user in filteredUsers"
                :key="user.id"
                :user="user"
                @view="viewUser"
            />
        </div>
    </section>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import Papa from "papaparse";
import ImportOption from "./ImportOption.vue";
import UserCard from "./UserCard.vue";

const users = ref([]);
const search = ref("");
const dropdownOpen = ref(false);

const filteredUsers = computed(() =>
    users.value.filter(
        (u) =>
            u.name.toLowerCase().includes(search.value.toLowerCase()) ||
            u.email.toLowerCase().includes(search.value.toLowerCase())
    )
);

const viewUser = (id) => (window.location.href = `/users/${id}`);

function onCSVImport(file) {
    if (!file) return;
    Papa.parse(file, {
        header: true,
        skipEmptyLines: true,
        complete: async ({ data }) => {
            const cleaned = data.filter(
                (row) => row.nom && row.prenom && row.email
            );
            await importUsers(cleaned);
        },
    });
}

function onVCFImport(file) {
    const reader = new FileReader();
    reader.onload = async (e) => {
        const contacts = parseVCF(e.target.result);
        await importUsers(contacts);
    };
    reader.readAsText(file);
}

async function importUsers(payload) {
    try {
        const res = await fetch("/api/import/users", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload),
        });
        const { users: imported } = await res.json();
        imported.forEach((u) => {
            users.value.push({
                id: u.id,
                name: u.fullname || `${u.nom} ${u.prenom}`,
                email: u.email,
                initials: (u.prenom?.[0] || "") + (u.nom?.[0] || "") || "??",
            });
        });
    } catch (err) {
        console.error("Erreur lors de l'import", err);
    } finally {
        dropdownOpen.value = false;
    }
}

function parseVCF(rawText) {
    const contacts = [];
    const cards = rawText
        .replace(/\r\n/g, "\n")
        .trim()
        .split(/BEGIN:VCARD/i)
        .slice(1);

    for (const card of cards) {
        const lines = card
            .split(/END:VCARD/i)[0]
            .split("\n")
            .map((l) => l.trim());
        let nom = "",
            prenom = "",
            email = "";

        for (const line of lines) {
            if (line.startsWith("N:") || line.startsWith("N;")) {
                const [n, p] = line.replace(/^N[^:]*:/, "").split(";");
                nom = decodeURIComponent(n.replace(/=/g, "%"));
                prenom = decodeURIComponent(p.replace(/=/g, "%"));
            } else if (line.startsWith("EMAIL")) {
                email = line.split(":").pop()?.trim() ?? "";
            }
        }

        if (nom && prenom && email) contacts.push({ nom, prenom, email });
    }

    return contacts;
}

onMounted(async () => {
    try {
        const res = await fetch("/api/users");
        const data = await res.json();

        // On mappe proprement les utilisateurs
        users.value = data.map((u) => ({
            id: u.id,
            name: u.fullname || `${u.nom} ${u.prenom}`,
            email: u.email,
            initials:
                (u.prenom?.[0] || "").toUpperCase() +
                (u.nom?.[0] || "").toUpperCase(),
        }));
    } catch (err) {
        console.error(
            "Erreur lors du chargement des utilisateurs initiaux",
            err
        );
    }
});
</script>
