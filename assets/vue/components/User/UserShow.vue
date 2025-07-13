<template>
    <div class="space-y-6">
        <!-- 🚀 Hero Bar -->
        <div
            class="flex flex-col md:flex-row md:justify-between items-start md:items-center bg-base-100 p-4 rounded-lg shadow"
        >
            <h1 class="text-3xl font-bold text-base-content">
                {{ user.prenom }} {{ user.nom }}
            </h1>
            <div class="mt-3 md:mt-0 flex gap-2">
                <template v-if="!editing">
                    <button @click="startEdit" class="btn btn-outline btn-sm">
                        ✏️ Éditer
                    </button>
                    <form
                        :action="deleteUrl"
                        method="post"
                        @submit="confirmDelete"
                        class="inline"
                    >
                        <input type="hidden" name="_token" :value="csrfToken" />
                        <button class="btn btn-error btn-outline btn-sm">
                            🗑️ Supprimer
                        </button>
                    </form>
                </template>

                <template v-else>
                    <button
                        @click="saveChanges"
                        class="btn btn-success btn-sm"
                        :disabled="saving"
                    >
                        {{ saving ? "Enregistrement…" : "Enregistrer" }}
                    </button>
                    <button @click="cancelEdit" class="btn btn-ghost btn-sm">
                        Annuler
                    </button>
                </template>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- 📝 Résumé (colonne de gauche) -->
            <div class="lg:col-span-1 space-y-4">
                <SectionCard icon="User" title="Détails utilisateurs">
                    <InfoRow label="Prénom" :value="user.prenom" />
                    <InfoRow label="Nom" :value="user.nom" />
                    <InfoRow label="Email" :value="user.email" />
                    <InfoRow label="Rôle" :value="roleLabel(user.role)" />
                    <InfoRow
                        label="Actif"
                        :value="user.isActive ? 'Oui' : 'Non'"
                    />
                </SectionCard>
            </div>

            <!-- 🖊️ Formulaire d'édition (colonne de droite) -->
            <div class="lg:col-span-2">
                <template v-if="editing">
                    <form @submit.prevent="saveChanges" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <FormControl label="Prénom" :error="errors.prenom">
                                <input
                                    v-model="form.prenom"
                                    class="input input-bordered w-full"
                                />
                            </FormControl>
                            <FormControl label="Nom" :error="errors.nom">
                                <input
                                    v-model="form.nom"
                                    class="input input-bordered w-full"
                                />
                            </FormControl>
                            <FormControl
                                label="Email"
                                :error="errors.email"
                                class="md:col-span-2"
                            >
                                <input
                                    type="email"
                                    v-model="form.email"
                                    class="input input-bordered w-full"
                                />
                            </FormControl>
                            <FormControl
                                label="Rôle"
                                :error="errors.role"
                                class="md:col-span-2"
                            >
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
                            </FormControl>
                            <FormControl
                                label="Compte actif"
                                class="md:col-span-2"
                            >
                                <label
                                    class="label cursor-pointer justify-start gap-2"
                                >
                                    <input
                                        type="checkbox"
                                        v-model="form.isActive"
                                        class="toggle toggle-primary"
                                    />
                                    <span class="label-text">Actif</span>
                                </label>
                            </FormControl>
                            <FormControl
                                label="Nouveau mot de passe"
                                :error="errors.password"
                                class="md:col-span-2"
                            >
                                <input
                                    type="password"
                                    v-model="form.password"
                                    placeholder="Laisser vide pour conserver l'actuel"
                                    class="input input-bordered w-full"
                                />
                            </FormControl>
                        </div>

                        <p v-if="errorGeneral" class="text-error">
                            {{ errorGeneral }}
                        </p>
                    </form>
                </template>

                <template v-else>
                    <!-- lorsque non-édition, affiche rien ici -->
                </template>
            </div>
        </div>
    </div>
</template>

<script>
import InfoRow from "@/components/InfoRow.vue";
import SectionCard from "@/components/SectionCard.vue";
import FormControl from "@/components/FormControl.vue";

export default {
    components: { InfoRow, SectionCard, FormControl },
    props: {
        user: { type: Object, required: true },
        saveUrl: { type: String, required: true },
        deleteUrl: { type: String, required: true },
        csrfToken: { type: String, required: true },
        isAdmin: { type: Boolean, default: false },
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
            errors: {},
            errorGeneral: "",
            saving: false,
            success: "",
        };
    },
    methods: {
        startEdit() {
            this.editing = true;
            this.errors = {};
            this.errorGeneral = "";
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
            this.errors = {};
            this.errorGeneral = "";
        },
        confirmDelete(e) {
            if (!confirm("Confirmer la suppression ?")) {
                e.preventDefault();
            }
        },
        async saveChanges() {
            this.saving = true;
            this.errors = {};
            this.errorGeneral = "";

            const payload = { ...this.form };
            if (!payload.password) delete payload.password;

            const res = await fetch(this.saveUrl, {
                method: "PATCH",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload),
            });

            if (res.ok) {
                this.success = "Modifications enregistrées.";
                this.editing = false;
                Object.assign(this.user, payload);
            } else if (res.status === 400) {
                const json = await res.json();
                this.errors = json.violations || {};
                this.errorGeneral = json.message || "";
            } else {
                this.errorGeneral = "Une erreur est survenue.";
            }
            this.saving = false;
        },
        roleLabel(value) {
            const map = {
                ADMIN_CENTRE: "Administrateur Centre",
                FORMATEUR: "Formateur",
                STAGIAIRE: "Stagiaire",
                ASSISTANT: "Assistant",
            };
            return map[value] || value;
        },
    },
};
</script>

<style scoped>
/* DaisyUI + Tailwind gèrent tout le style */
</style>
