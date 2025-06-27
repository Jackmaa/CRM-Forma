<template>
    <section class="flex flex-col p-6 bg-gray-50 h-full w-full">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-semibold">Paramètres</h2>
        </div>

        <div class="mx-auto space-y-8">
            <!-- Profile Settings -->
            <div>
                <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                    <User class="w-5 h-5 text-gray-500" />
                    Profil
                </h3>
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700"
                            >Nom d’utilisateur</label
                        >
                        <input
                            v-model="settings.username"
                            type="text"
                            class="mt-1 w-80 border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700"
                            >Email</label
                        >
                        <input
                            v-model="settings.email"
                            type="email"
                            class="mt-1 w-80 border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300"
                        />
                    </div>
                </div>
            </div>

            <!-- Security Settings -->
            <div>
                <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                    <Lock class="w-5 h-5 text-gray-500" />
                    Sécurité
                </h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700"
                            >Nouveau mot de passe</label
                        >
                        <input
                            v-model="settings.password"
                            type="password"
                            class="mt-1 w-80 border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300"
                        />
                    </div>
                </div>
            </div>

            <!-- Notification Settings -->
            <div>
                <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                    <Bell class="w-5 h-5 text-gray-500" />
                    Notifications
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

            <!-- Appearance Settings -->
            <div>
                <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                    <Sun class="w-5 h-5 text-yellow-500" />
                    Apparence
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
            <button
                @click="saveSettings"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition w-fit"
            >
                Enregistrer
            </button>
        </div>
    </section>
</template>

<script setup>
import { reactive, onMounted } from "vue";
import { User, Lock, Bell, Sun } from "lucide-vue-next";

const settings = reactive({
    username: "",
    email: "",
    password: "",
    notifications: { email: true, sms: false },
    theme: "light",
});

function saveSettings() {
    // TODO: envoyer settings via API
    console.log(
        "Sauvegarde des paramètres:",
        JSON.parse(JSON.stringify(settings))
    );
}

onMounted(() => {
    // TODO: charger les paramètres existants via API
    // Exemple :
    // fetch('/api/settings').then(res => res.json()).then(data => Object.assign(settings, data))
});
</script>
