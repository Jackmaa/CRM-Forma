<template>
    <section class="p-6 bg-gray-100 h-full overflow-auto">
        <h2 class="text-3xl font-bold mb-6">Paramètres du compte</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Profil Card -->
            <div class="col-span-2 bg-white rounded-lg shadow p-6 space-y-4">
                <div class="flex items-center mb-4">
                    <UserIcon class="w-6 h-6 text-blue-600 mr-2" />
                    <h3 class="text-xl font-semibold">Profil</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700"
                            >Nom d’utilisateur</label
                        >
                        <input
                            v-model="settings.username"
                            type="text"
                            class="mt-1 w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700"
                            >Email</label
                        >
                        <input
                            v-model="settings.email"
                            type="email"
                            class="mt-1 w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                        />
                    </div>
                </div>
            </div>

            <!-- Security Card -->
            <div class="bg-white rounded-lg shadow p-6 space-y-4">
                <div class="flex items-center mb-4">
                    <LockIcon class="w-6 h-6 text-red-600 mr-2" />
                    <h3 class="text-xl font-semibold">Sécurité</h3>
                </div>
                <div class="space-y-4">
                    <div
                        v-if="settings.forcePasswordReset"
                        class="p-4 bg-yellow-50 border-l-4 border-yellow-400 text-yellow-800 rounded"
                    >
                        Vous devez changer votre mot de passe avant de
                        continuer.
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700"
                            >Nouveau mot de passe</label
                        >
                        <input
                            v-model="settings.password"
                            type="password"
                            class="mt-1 w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-red-400"
                            :disabled="
                                settings.forcePasswordReset &&
                                !settings.password
                            "
                        />
                    </div>
                </div>
            </div>

            <!-- Notifications & Theme Card -->
            <div class="bg-white rounded-lg shadow p-6 space-y-6">
                <!-- Notifications -->
                <div>
                    <div class="flex items-center mb-3">
                        <BellIcon class="w-6 h-6 text-green-600 mr-2" />
                        <h3 class="text-lg font-semibold">Notifications</h3>
                    </div>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input
                                type="checkbox"
                                v-model="settings.notifications.email"
                                class="h-5 w-5 text-green-600"
                            />
                            <span class="ml-2">Email</span>
                        </label>
                        <label class="flex items-center">
                            <input
                                type="checkbox"
                                v-model="settings.notifications.sms"
                                class="h-5 w-5 text-green-600"
                            />
                            <span class="ml-2">SMS</span>
                        </label>
                    </div>
                </div>
                <!-- Theme -->
                <div>
                    <div class="flex items-center mb-3">
                        <SunIcon class="w-6 h-6 text-yellow-500 mr-2" />
                        <h3 class="text-lg font-semibold">Apparence</h3>
                    </div>
                    <div class="flex items-center space-x-6">
                        <label class="flex items-center">
                            <input
                                type="radio"
                                value="light"
                                v-model="settings.theme"
                                class="h-5 w-5 text-blue-600"
                            />
                            <span class="ml-2">Clair</span>
                        </label>
                        <label class="flex items-center">
                            <input
                                type="radio"
                                value="dark"
                                v-model="settings.theme"
                                class="h-5 w-5 text-blue-600"
                            />
                            <span class="ml-2">Sombre</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Save Button -->
        <div class="mt-6 flex justify-end">
            <button
                @click="saveSettings"
                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition disabled:opacity-50"
                :disabled="settings.forcePasswordReset && !settings.password"
            >
                Enregistrer les modifications
            </button>
        </div>
    </section>
</template>

<script setup>
import { reactive, onMounted } from "vue";
import {
    User as UserIcon,
    Lock as LockIcon,
    Bell as BellIcon,
    Sun as SunIcon,
} from "lucide-vue-next";

const token = localStorage.getItem("auth_token") || "";
const settings = reactive({
    username: "",
    email: "",
    password: "",
    notifications: { email: true, sms: false },
    theme: "light",
    forcePasswordReset: false,
});

onMounted(async () => {
    const res = await fetch("/api/user/settings", {
        headers: { Authorization: `Bearer ${token}` },
    });
    if (res.ok) Object.assign(settings, await res.json());
});

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
        const updated = await fetch("/api/user/settings", {
            headers: { Authorization: `Bearer ${token}` },
        }).then((r) => r.json());
        Object.assign(settings, updated);
    }
}
</script>

<style scoped>
/* Ajoute une légère ombre d’élévation pour les cartes */
.shadow {
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1), 0 1px 2px rgba(0, 0, 0, 0.06);
}
</style>
