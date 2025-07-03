<template>
    <section class="p-6 bg-white shadow rounded space-y-6">
        <div v-if="!editing">
            <h1 class="text-2xl font-semibold">
                {{ user.prenom }} {{ user.nom }}
            </h1>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <p><strong>Prénom :</strong> {{ user.prenom }}</p>
                    <p><strong>Nom :</strong> {{ user.nom }}</p>
                    <p><strong>Email :</strong> {{ user.email }}</p>
                    <p><strong>Rôle :</strong> {{ user.role }}</p>
                    <p>
                        <strong>Actif :</strong>
                        {{ user.isActive ? "Oui" : "Non" }}
                    </p>
                </div>
            </div>

            <div v-if="isAdmin" class="mt-6 flex gap-2">
                <button
                    @click="startEdit"
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                >
                    Modifier
                </button>

                <form :action="deleteUrl" method="post" @submit="confirmDelete">
                    <input type="hidden" name="_token" :value="csrfToken" />
                    <button
                        type="submit"
                        class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
                    >
                        Supprimer
                    </button>
                </form>
            </div>
        </div>

        <!-- Mode édition -->
        <form v-else @submit.prevent="saveChanges" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm">Prénom</label>
                    <input
                        v-model="form.prenom"
                        class="w-full border rounded px-2 py-1"
                    />
                </div>
                <div>
                    <label class="block text-sm">Nom</label>
                    <input
                        v-model="form.nom"
                        class="w-full border rounded px-2 py-1"
                    />
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm">Email</label>
                    <input
                        v-model="form.email"
                        type="email"
                        class="w-full border rounded px-2 py-1"
                    />
                </div>
                <div>
                    <label class="block text-sm">Rôle</label>
                    <select
                        v-model="form.role"
                        class="w-full border rounded px-2 py-1"
                    >
                        <option value="ADMIN_CENTRE">
                            Administrateur Centre
                        </option>
                        <option value="FORMATEUR">Formateur</option>
                        <option value="STAGIAIRE">Stagiaire</option>
                        <option value="ASSISTANT">Assistant</option>
                    </select>
                </div>
                <div>
                    <label class="inline-flex items-center">
                        <input
                            type="checkbox"
                            v-model="form.isActive"
                            class="form-checkbox h-5 w-5 text-blue-600"
                        />
                        <span class="ml-2">Compte actif</span>
                    </label>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm">Nouveau mot de passe</label>
                    <input
                        v-model="form.password"
                        type="password"
                        placeholder="Laisser vide pour conserver l'actuel"
                        class="w-full border rounded px-2 py-1"
                    />
                </div>
            </div>

            <div class="flex gap-2 mt-3">
                <button
                    type="submit"
                    class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
                    :disabled="saving"
                >
                    <span v-if="saving">Sauvegarde...</span>
                    <span v-else>Enregistrer</span>
                </button>
                <button
                    type="button"
                    @click="cancelEdit"
                    class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400"
                >
                    Annuler
                </button>
            </div>

            <p v-if="error" class="text-red-600">{{ error }}</p>
            <p v-if="success" class="text-green-600">{{ success }}</p>
        </form>
    </section>
</template>

<script>
export default {
    props: {
        user: Object,
        saveUrl: String,
        deleteUrl: String,
        csrfToken: String,
        isAdmin: Boolean,
    },
    data() {
        return {
            editing: false,
            form: {
                prenom: this.user.prenom,
                nom: this.user.nom,
                email: this.user.email,
                role: this.user.role,
                isActive: this.user.isActive,
                password: "",
            },
            saving: false,
            error: "",
            success: "",
        };
    },
    methods: {
        startEdit() {
            this.editing = true;
            this.error = "";
            this.success = "";
        },
        cancelEdit() {
            this.editing = false;
            this.form = {
                prenom: this.user.prenom,
                nom: this.user.nom,
                email: this.user.email,
                role: this.user.role,
                isActive: this.user.isActive,
                password: "",
            };
        },
        confirmDelete(e) {
            if (
                !confirm("Êtes-vous sûr de vouloir supprimer cet utilisateur ?")
            ) {
                e.preventDefault();
            }
        },
        async saveChanges() {
            this.saving = true;
            this.error = "";
            this.success = "";
            try {
                const payload = { ...this.form };
                // Ne pas envoyer password vide
                if (!payload.password) {
                    delete payload.password;
                }
                const res = await fetch(this.saveUrl, {
                    method: "PATCH",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(payload),
                });
                if (!res.ok) throw new Error("Erreur lors de la sauvegarde");
                this.success = "Modifications enregistrées.";
                this.editing = false;
                // Met à jour localement la vue
                Object.assign(this.user, payload);
            } catch (err) {
                this.error = err.message;
            } finally {
                this.saving = false;
            }
        },
    },
};
</script>
