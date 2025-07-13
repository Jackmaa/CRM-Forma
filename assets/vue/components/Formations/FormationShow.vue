<template>
    <div class="card bg-base-100 shadow-lg p-6 space-y-6">
        <!-- Affichage ou édition -->
        <template v-if="!editing">
            <h1 class="text-2xl font-semibold text-base-content">
                {{ formation.titre }}
            </h1>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Informations principales -->
                <div class="space-y-2">
                    <p class="text-base-content opacity-60">
                        <strong>Thématique :</strong> {{ formation.thematique }}
                    </p>
                    <p class="text-base-content opacity-60">
                        <strong>Niveau :</strong> {{ formation.niveau }}
                    </p>
                    <p class="text-base-content opacity-60">
                        <strong>Durée :</strong> {{ formation.duree }} h
                    </p>
                    <p class="text-base-content opacity-60">
                        <strong>Tarif :</strong> {{ formation.tarif }} €
                    </p>
                    <p class="text-base-content opacity-60">
                        <strong>Créé le :</strong>
                        {{ formatDate(formation.createdAt) }}
                    </p>
                    <p
                        v-if="formation.responsable"
                        class="text-base-content opacity-60"
                    >
                        <strong>Responsable :</strong>
                        {{ formation.responsable }}
                    </p>
                </div>
                <!-- Descriptions -->
                <div class="space-y-4">
                    <div>
                        <p class="font-medium text-base-content">Prérequis</p>
                        <div
                            class="p-4 bg-base-200 rounded whitespace-pre-line text-base-content"
                        >
                            {{ formation.prerequis }}
                        </div>
                    </div>
                    <div>
                        <p class="font-medium text-base-content">Description</p>
                        <div
                            class="p-4 bg-base-200 rounded whitespace-pre-line text-base-content"
                        >
                            {{ formation.description }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modalités et objectifs -->
            <div v-if="formation.modalites?.length" class="space-y-2">
                <p class="font-semibold text-base-content">
                    Modalités pédagogiques
                </p>
                <ul class="list-disc list-inside text-base-content opacity-80">
                    <li v-for="(m, i) in formation.modalites" :key="i">
                        {{ m }}
                    </li>
                </ul>
            </div>
            <div v-if="formation.objectifs?.length" class="space-y-2">
                <p class="font-semibold text-base-content">
                    Objectifs pédagogiques
                </p>
                <ul class="list-disc list-inside text-base-content opacity-80">
                    <li v-for="(o, i) in formation.objectifs" :key="i">
                        {{ o }}
                    </li>
                </ul>
            </div>

            <!-- Actions Admin -->
            <div v-if="isAdmin" class="flex gap-4 mt-4">
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
        </template>

        <!-- Formulaire édition -->
        <template v-else>
            <form @submit.prevent="saveChanges" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label"
                            ><span class="label-text">Titre</span></label
                        >
                        <input
                            v-model="form.titre"
                            class="input input-bordered"
                        />
                    </div>
                    <div class="form-control">
                        <label class="label"
                            ><span class="label-text">Thématique</span></label
                        >
                        <input
                            v-model="form.thematique"
                            class="input input-bordered"
                        />
                    </div>
                    <div class="form-control">
                        <label class="label"
                            ><span class="label-text">Niveau</span></label
                        >
                        <input
                            v-model="form.niveau"
                            class="input input-bordered"
                        />
                    </div>
                    <div class="form-control">
                        <label class="label"
                            ><span class="label-text">Durée (h)</span></label
                        >
                        <input
                            v-model.number="form.duree"
                            type="number"
                            class="input input-bordered"
                        />
                    </div>
                    <div class="form-control">
                        <label class="label"
                            ><span class="label-text">Tarif (€)</span></label
                        >
                        <input
                            v-model.number="form.tarif"
                            type="number"
                            class="input input-bordered"
                        />
                    </div>
                    <div class="form-control md:col-span-2">
                        <label class="label"
                            ><span class="label-text">Prérequis</span></label
                        >
                        <textarea
                            v-model="form.prerequis"
                            class="textarea textarea-bordered"
                            rows="3"
                        ></textarea>
                    </div>
                    <div class="form-control md:col-span-2">
                        <label class="label"
                            ><span class="label-text">Description</span></label
                        >
                        <textarea
                            v-model="form.description"
                            class="textarea textarea-bordered"
                            rows="4"
                        ></textarea>
                    </div>
                    <!-- Liste dynamique de modalités -->
                    <div class="md:col-span-2">
                        <label class="label"
                            ><span class="label-text"
                                >Modalités pédagogiques</span
                            ></label
                        >
                        <div class="space-y-2">
                            <div
                                v-for="(m, idx) in form.modalites"
                                :key="idx"
                                class="flex items-center gap-2"
                            >
                                <input
                                    v-model="form.modalites[idx]"
                                    class="input input-bordered flex-1"
                                />
                                <button
                                    type="button"
                                    @click="removeModalite(idx)"
                                    class="btn btn-sm btn-ghost"
                                >
                                    ✕
                                </button>
                            </div>
                            <button
                                type="button"
                                @click="addModalite"
                                class="btn btn-outline btn-sm"
                            >
                                + Ajouter modalité
                            </button>
                        </div>
                    </div>
                    <!-- Liste dynamique d'objectifs -->
                    <div class="md:col-span-2">
                        <label class="label"
                            ><span class="label-text"
                                >Objectifs pédagogiques</span
                            ></label
                        >
                        <div class="space-y-2">
                            <div
                                v-for="(o, idx) in form.objectifs"
                                :key="idx"
                                class="flex items-center gap-2"
                            >
                                <input
                                    v-model="form.objectifs[idx]"
                                    class="input input-bordered flex-1"
                                />
                                <button
                                    type="button"
                                    @click="removeObjectif(idx)"
                                    class="btn btn-sm btn-ghost"
                                >
                                    ✕
                                </button>
                            </div>
                            <button
                                type="button"
                                @click="addObjectif"
                                class="btn btn-outline btn-sm"
                            >
                                + Ajouter objectif
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Actions formulaire -->
                <div class="flex gap-4">
                    <button
                        type="submit"
                        class="btn btn-success"
                        :disabled="saving"
                    >
                        {{ saving ? "Sauvegarde..." : "Enregistrer" }}
                    </button>
                    <button
                        type="button"
                        @click="cancelEdit"
                        class="btn btn-ghost"
                    >
                        Annuler
                    </button>
                </div>

                <p v-if="error" class="text-error">{{ error }}</p>
                <p v-if="success" class="text-success">{{ success }}</p>
            </form>
        </template>
    </div>
</template>

<script>
export default {
    props: {
        formation: { type: Object, required: true },
        saveUrl: { type: String, required: true },
        deleteUrl: { type: String, required: true },
        csrfToken: { type: String, required: true },
        isAdmin: { type: Boolean, default: false },
    },
    data() {
        return {
            editing: false,
            form: { ...this.formation },
            saving: false,
            error: "",
            success: "",
        };
    },
    methods: {
        startEdit() {
            this.editing = true;
            this.success = "";
            this.error = "";
        },
        cancelEdit() {
            this.editing = false;
            this.form = { ...this.formation };
        },
        addModalite() {
            this.form.modalites = this.form.modalites || [];
            this.form.modalites.push("");
        },
        removeModalite(i) {
            this.form.modalites.splice(i, 1);
        },
        addObjectif() {
            this.form.objectifs = this.form.objectifs || [];
            this.form.objectifs.push("");
        },
        removeObjectif(i) {
            this.form.objectifs.splice(i, 1);
        },
        async saveChanges() {
            this.saving = true;
            this.error = "";
            this.success = "";
            try {
                const res = await fetch(this.saveUrl, {
                    method: "PATCH",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(this.form),
                });
                if (!res.ok) throw new Error("Erreur lors de la sauvegarde");
                this.success = "Modifications enregistrées.";
                this.editing = false;
            } catch (err) {
                this.error = err.message;
            } finally {
                this.saving = false;
            }
        },
        confirmDelete(e) {
            if (!confirm("Confirmer la suppression ?")) e.preventDefault();
        },
        formatDate(dateStr) {
            const d = new Date(dateStr);
            return (
                d.toLocaleDateString("fr-FR") +
                " " +
                d.toLocaleTimeString("fr-FR")
            );
        },
    },
};
</script>

<style scoped>
/* DaisyUI fournit tous les styles, pas de CSS custom nécessaire */
</style>
