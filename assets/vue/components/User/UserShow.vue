<template>
    <section class="card bg-base-100 shadow-lg p-6 space-y-6">
        <div v-if="!editing">
            <h1 class="text-2xl font-semibold text-base-content">
                {{ user.prenom }} {{ user.nom }}
            </h1>

            <div
                class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 text-base-content"
            >
                <div class="space-y-2">
                    <p>
                        <span class="font-semibold">Prénom :</span>
                        {{ user.prenom }}
                    </p>
                    <p>
                        <span class="font-semibold">Nom :</span> {{ user.nom }}
                    </p>
                    <p>
                        <span class="font-semibold">Email :</span>
                        {{ user.email }}
                    </p>
                    <p>
                        <span class="font-semibold">Rôle :</span>
                        {{ user.role }}
                    </p>
                    <p>
                        <span class="font-semibold">Actif :</span>
                        {{ user.isActive ? "Oui" : "Non" }}
                    </p>
                </div>
            </div>

            <div v-if="isAdmin" class="flex gap-2 mt-6">
                <button @click="startEdit" class="btn btn-primary btn-sm">
                    Modifier
                </button>
                <form :action="deleteUrl" method="post" @submit="confirmDelete">
                    <input type="hidden" name="_token" :value="csrfToken" />
                    <button type="submit" class="btn btn-error btn-sm">
                        Supprimer
                    </button>
                </form>
            </div>
        </div>

        <form v-else @submit.prevent="saveChanges" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control">
                    <label class="label">
                        <span class="label-text text-base-content">Prénom</span>
                    </label>
                    <input
                        v-model="form.prenom"
                        class="input input-bordered w-full"
                    />
                </div>
                <div class="form-control">
                    <label class="label">
                        <span class="label-text text-base-content">Nom</span>
                    </label>
                    <input
                        v-model="form.nom"
                        class="input input-bordered w-full"
                    />
                </div>
                <div class="form-control md:col-span-2">
                    <label class="label">
                        <span class="label-text text-base-content">Email</span>
                    </label>
                    <input
                        type="email"
                        v-model="form.email"
                        class="input input-bordered w-full"
                    />
                </div>
                <div class="form-control">
                    <label class="label">
                        <span class="label-text text-base-content">Rôle</span>
                    </label>
                    <select
                        v-model="form.role"
                        class="select select-bordered w-full"
                    >
                        <option value="ADMIN_CENTRE">
                            Administrateur Centre
                        </option>
                        <option value="FORMATEUR">Formateur</option>
                        <option value="STAGIAIRE">Stagiaire</option>
                        <option value="ASSISTANT">Assistant</option>
                    </select>
                </div>
                <div class="form-control">
                    <label class="label cursor-pointer">
                        <span class="label-text text-base-content"
                            >Compte actif</span
                        >
                        <input
                            type="checkbox"
                            v-model="form.isActive"
                            class="toggle toggle-primary"
                        />
                    </label>
                </div>
                <div class="form-control md:col-span-2">
                    <label class="label">
                        <span class="label-text text-base-content"
                            >Nouveau mot de passe</span
                        >
                    </label>
                    <input
                        type="password"
                        v-model="form.password"
                        placeholder="Laisser vide pour conserver l'actuel"
                        class="input input-bordered w-full"
                    />
                </div>
            </div>

            <div class="flex gap-2 mt-3">
                <button
                    type="submit"
                    :disabled="saving"
                    class="btn btn-success btn-sm"
                >
                    <span v-if="saving">Sauvegarde...</span>
                    <span v-else>Enregistrer</span>
                </button>
                <button
                    type="button"
                    @click="cancelEdit"
                    class="btn btn-ghost btn-sm"
                >
                    Annuler
                </button>
            </div>

            <p v-if="error" class="text-error">{{ error }}</p>
            <p v-if="success" class="text-success">{{ success }}</p>
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
            Object.assign(this.form, {
                prenom: this.user.prenom,
                nom: this.user.nom,
                email: this.user.email,
                role: this.user.role,
                isActive: this.user.isActive,
                password: "",
            });
            this.error = "";
            this.success = "";
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
                if (!payload.password) delete payload.password;
                const res = await fetch(this.saveUrl, {
                    method: "PATCH",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(payload),
                });
                if (!res.ok) throw new Error("Erreur lors de la sauvegarde");
                this.success = "Modifications enregistrées.";
                this.editing = false;
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

<style scoped>
/* DaisyUI gère les styles */
</style>
