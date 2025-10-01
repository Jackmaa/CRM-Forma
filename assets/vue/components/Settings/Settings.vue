<template>
    <section class="p-6 bg-base-200 h-full overflow-auto">
        <h2 class="text-3xl font-bold text-base-content mb-6">
            Paramètres du compte
        </h2>

        <!-- Tabs Navigation -->
        <div class="tabs mb-4">
            <button
                :class="[
                    'tab tab-lifted',
                    activeTab === 'profil' ? 'tab-active' : '',
                ]"
                @click="activeTab = 'profil'"
            >
                Profil
            </button>
            <button
                :class="[
                    'tab tab-lifted',
                    activeTab === 'securite' ? 'tab-active' : '',
                ]"
                @click="activeTab = 'securite'"
            >
                Sécurité
            </button>
            <button
                :class="[
                    'tab tab-lifted',
                    activeTab === 'notifications' ? 'tab-active' : '',
                ]"
                @click="activeTab = 'notifications'"
            >
                Notifications & Apparence
            </button>
        </div>

        <form @submit.prevent="saveSettings" class="space-y-6">
            <!-- Profil -->
            <div v-show="activeTab === 'profil'">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Nom d’utilisateur</span>
                        </label>
                        <input
                            v-model="settings.username"
                            type="text"
                            placeholder="Entrez votre nom d’utilisateur"
                            class="input input-bordered w-full"
                            required
                        />
                    </div>
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Email</span>
                        </label>
                        <input
                            v-model="settings.email"
                            type="email"
                            placeholder="exemple@domaine.com"
                            class="input input-bordered w-full"
                            required
                        />
                    </div>
                </div>
            </div>

            <!-- Sécurité -->
            <div v-show="activeTab === 'securite'">
                <div
                    v-if="settings.forcePasswordReset"
                    class="alert alert-warning"
                >
                    Vous devez définir un nouveau mot de passe.
                </div>
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Nouveau mot de passe</span>
                    </label>
                    <input
                        v-model="settings.password"
                        type="password"
                        placeholder="••••••••"
                        class="input input-bordered w-full"
                        :required="settings.forcePasswordReset"
                    />
                </div>
            </div>

            <!-- Notifications & Apparence -->
            <div v-show="activeTab === 'notifications'">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label cursor-pointer">
                            <span class="label-text">Notifications Email</span>
                            <input
                                type="checkbox"
                                v-model="settings.notifications.email"
                                class="toggle toggle-primary"
                            />
                        </label>
                    </div>
                    <div class="form-control">
                        <label class="label cursor-pointer">
                            <span class="label-text">Notifications SMS</span>
                            <input
                                type="checkbox"
                                v-model="settings.notifications.sms"
                                class="toggle toggle-primary"
                            />
                        </label>
                    </div>
                </div>
                <div class="divider"></div>
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Thème de l’application</span>
                    </label>
                    <ThemeSwitcher />
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end items-center space-x-4">
                <button type="button" class="btn btn-ghost" @click="resetForm">
                    Annuler
                </button>
                <button
                    type="submit"
                    class="btn btn-primary"
                    :class="{ loading: saving }"
                    :disabled="
                        saving ||
                        (settings.forcePasswordReset && !settings.password)
                    "
                >
                    <span v-if="!saving">Enregistrer</span>
                    <span v-else>Enregistrement...</span>
                </button>
            </div>
        </form>
    </section>
</template>

<script setup>
/**
 * Composant de gestion des paramètres utilisateur (profil, sécurité, notifications, apparence).
 *
 * Affiche des onglets pour modifier le profil, le mot de passe, les notifications et le thème.
 *
 * État local :
 * - settings : objet réactif contenant les infos utilisateur
 * - activeTab : onglet actif
 * - saving : état de sauvegarde
 */
import { reactive, ref, onMounted, onBeforeUnmount } from "vue";
import ThemeSwitcher from "@/components/ThemeSwitcher.vue";
import { getJson, putJson } from "@/utils/apiFetch";

const settings = reactive({
    username: "",
    email: "",
    password: "",
    notifications: { email: true, sms: false },
    theme: "light",
    forcePasswordReset: false,
});
const activeTab = ref("profil");
const saving = ref(false);

// Chargement initial des paramètres utilisateur
onMounted(async () => {
    if (typeof window !== "undefined" && window.awaitJwt) {
        try {
            await window.awaitJwt;
        } catch {}
    }
    window.addEventListener("auth:changed", loadSettings);
    await loadSettings();
});

onBeforeUnmount(() => {
    window.removeEventListener("auth:changed", loadSettings);
});

/**
 * Réinitialise le formulaire et recharge les données depuis le serveur.
 */
async function loadSettings() {
    const data = await getJson(`/user/settings?t=${Date.now()}`);
    if (data) Object.assign(settings, data);
}

function resetForm() {
    activeTab.value = "profil";
    settings.password = "";
    // reload from server
    loadSettings();
}

/**
 * Sauvegarde les paramètres utilisateur via l'API.
 */
async function saveSettings() {
    saving.value = true;
    const payload = {
        username: settings.username,
        email: settings.email,
        password: settings.password,
        notifications: settings.notifications,
        theme: settings.theme,
    };
    await putJson("/user/settings", payload);
    await loadSettings();
    saving.value = false;
}
</script>

<style scoped>
/* Grâce à DaisyUI */
</style>
