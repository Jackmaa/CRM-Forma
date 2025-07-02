<template>
    <section class="flex flex-col p-6 bg-gray-50 h-full w-full">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-semibold">Paramètres</h2>
        </div>

        <div class="mx-auto space-y-8 max-w-lg">
            <!-- Bannière de forçage -->
            <div
                v-if="settings.forcePasswordReset"
                class="mb-4 p-4 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 rounded"
            >
                Vous devez changer votre mot de passe avant de continuer.
            </div>

            <!-- Profil / Notifications / Apparence groupés et désactivés si besoin -->
            <div
                :class="{
                    'opacity-50 pointer-events-none':
                        settings.forcePasswordReset,
                }"
            >
                <!-- Profil -->
                <div class="space-y-2">
                    <h3
                        class="text-lg font-semibold mb-2 flex items-center gap-2"
                    >
                        <User class="w-5 h-5 text-gray-500" /> Profil
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700"
                            >
                                Nom d’utilisateur
                            </label>
                            <input
                                v-model="settings.username"
                                type="text"
                                class="mt-1 w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300"
                            />
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700"
                            >
                                Email
                            </label>
                            <input
                                v-model="settings.email"
                                type="email"
                                class="mt-1 w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300"
                            />
                        </div>
                    </div>
                </div>

                <!-- Notifications -->
                <div class="space-y-2">
                    <h3
                        class="text-lg font-semibold mb-2 flex items-center gap-2"
                    >
                        <Bell class="w-5 h-5 text-gray-500" /> Notifications
                    </h3>
                    <div class="flex flex-col space-y-2">
                        <label class="inline-flex items-center">
                            <input
                                type="checkbox"
                                v-model="settings.notifications.email"
                                class="form-checkbox h-5 w-5 text-blue-600"
                            />
                            <span class="ml-2 text-gray-700">Email</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input
                                type="checkbox"
                                v-model="settings.notifications.sms"
                                class="form-checkbox h-5 w-5 text-blue-600"
                            />
                            <span class="ml-2 text-gray-700">SMS</span>
                        </label>
                    </div>
                </div>

                <!-- Apparence -->
                <div class="space-y-2">
                    <h3
                        class="text-lg font-semibold mb-2 flex items-center gap-2"
                    >
                        <Sun class="w-5 h-5 text-yellow-500" /> Apparence
                    </h3>
                    <div class="flex items-center gap-4">
                        <label class="inline-flex items-center">
                            <input
                                type="radio"
                                value="light"
                                v-model="settings.theme"
                                class="form-radio h-5 w-5 text-blue-600"
                            />
                            <span class="ml-2 text-gray-700">Clair</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input
                                type="radio"
                                value="dark"
                                v-model="settings.theme"
                                class="form-radio h-5 w-5 text-blue-600"
                            />
                            <span class="ml-2 text-gray-700">Sombre</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Mot de passe (toujours actif) -->
            <div>
                <h3 class="text-lg font-semibold mb-2 flex items-center gap-2">
                    <Lock class="w-5 h-5 text-gray-500" /> Sécurité
                </h3>
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Nouveau mot de passe
                    </label>
                    <input
                        v-model="settings.password"
                        type="password"
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300"
                    />
                </div>
            </div>

            <!-- Bouton Enregistrer -->
            <button
                @click="saveSettings"
                :disabled="settings.forcePasswordReset && !settings.password"
                class="w-fit bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
            >
                Enregistrer
            </button>
        </div>
    </section>
</template>

<script setup>
import { reactive, onMounted } from "vue";
import { User, Lock, Bell, Sun } from "lucide-vue-next";

// Remplace par la récup de ton token actuel (store, cookie…)
const token = localStorage.getItem("auth_token") || "";

const settings = reactive({
    username: "",
    email: "",
    password: "",
    notifications: { email: true, sms: false },
    theme: "light",
    forcePasswordReset: false,
});

// Charge l’API au montage
onMounted(async () => {
    const res = await fetch("/api/user/settings", {
        headers: { Authorization: `Bearer ${token}` },
    });
    const data = await res.json();
    Object.assign(settings, data);
});

// Sauvegarde en PUT
async function saveSettings() {
    const payload = {
        username: settings.username,
        email: settings.email,
        password: settings.password,
        notifications: settings.notifications,
        theme: settings.theme,
    };

    const res = await fetch("/api/user/settings", {
        method: "PUT",
        headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify(payload),
    });

    if (res.ok) {
        // Recharge pour récupérer forcePasswordReset mis à false
        const updated = await (
            await fetch("/api/user/settings", {
                headers: { Authorization: `Bearer ${token}` },
            })
        ).json();
        Object.assign(settings, updated);
    }
}
</script>
