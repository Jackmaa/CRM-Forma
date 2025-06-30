<template>
    <section class="p-6 bg-white shadow rounded space-y-6">
        <div v-if="!editing">
            <h1 class="text-2xl font-semibold">{{ formation.titre }}</h1>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <p>
                        <strong>Thématique :</strong> {{ formation.thematique }}
                    </p>
                    <p><strong>Niveau :</strong> {{ formation.niveau }}</p>
                    <p><strong>Durée :</strong> {{ formation.duree }} heures</p>
                    <p><strong>Tarif :</strong> {{ formation.tarif }} €</p>
                    <p>
                        <strong>Date de création :</strong>
                        {{ formatDate(formation.createdAt) }}
                    </p>
                    <p v-if="formation.responsable">
                        <strong>Responsable :</strong>
                        {{ formation.responsable }}
                    </p>
                </div>
                <div>
                    <p><strong>Prérequis :</strong></p>
                    <div
                        class="mt-1 p-2 bg-gray-50 border rounded whitespace-pre-line"
                    >
                        {{ formation.prerequis }}
                    </div>
                    <p class="mt-4"><strong>Description :</strong></p>
                    <div
                        class="mt-1 p-2 bg-gray-50 border rounded whitespace-pre-line"
                    >
                        {{ formation.description }}
                    </div>
                </div>
            </div>

            <div v-if="formation.modalites && formation.modalites.length">
                <h2 class="text-lg font-semibold mt-4">
                    Modalités pédagogiques
                </h2>
                <ul class="list-disc list-inside mt-2">
                    <li v-for="(m, i) in formation.modalites" :key="i">
                        {{ m }}
                    </li>
                </ul>
            </div>

            <div v-if="formation.objectifs && formation.objectifs.length">
                <h2 class="text-lg font-semibold mt-4">
                    Objectifs pédagogiques
                </h2>
                <ul class="list-disc list-inside mt-2">
                    <li v-for="(o, i) in formation.objectifs" :key="i">
                        {{ o }}
                    </li>
                </ul>
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
        <form v-else @submit.prevent="saveChanges" class="space-y-2">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm">Titre</label>
                    <input
                        v-model="form.titre"
                        class="w-full border rounded px-2 py-1"
                    />
                </div>
                <div>
                    <label class="block text-sm">Thématique</label>
                    <input
                        v-model="form.thematique"
                        class="w-full border rounded px-2 py-1"
                    />
                </div>
                <div>
                    <label class="block text-sm">Niveau</label>
                    <input
                        v-model="form.niveau"
                        class="w-full border rounded px-2 py-1"
                    />
                </div>
                <div>
                    <label class="block text-sm">Durée (heures)</label>
                    <input
                        v-model="form.duree"
                        type="number"
                        class="w-full border rounded px-2 py-1"
                    />
                </div>
                <div>
                    <label class="block text-sm">Tarif (€)</label>
                    <input
                        v-model="form.tarif"
                        type="number"
                        class="w-full border rounded px-2 py-1"
                    />
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm">Prérequis</label>
                    <textarea
                        v-model="form.prerequis"
                        class="w-full border rounded px-2 py-1"
                    ></textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm">Description</label>
                    <textarea
                        v-model="form.description"
                        class="w-full border rounded px-2 py-1"
                    ></textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm">Modalités pédagogiques</label>
                    <div class="space-y-2">
                        <div
                            v-for="(modalite, index) in form.modalites"
                            :key="index"
                            class="flex items-center gap-2"
                        >
                            <input
                                v-model="form.modalites[index]"
                                class="flex-1 border rounded px-2 py-1"
                            />
                            <button
                                type="button"
                                @click="removeModalite(index)"
                                class="text-red-600 hover:text-red-800"
                            >
                                ✕
                            </button>
                        </div>
                        <button
                            type="button"
                            @click="addModalite"
                            class="mt-2 px-3 py-1 bg-blue-100 text-blue-700 rounded"
                        >
                            + Ajouter une modalité
                        </button>
                    </div>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm">Objectifs pédagogiques</label>
                    <div class="space-y-2">
                        <div
                            v-for="(objectif, index) in form.objectifs"
                            :key="index"
                            class="flex items-center gap-2"
                        >
                            <input
                                v-model="form.objectifs[index]"
                                class="flex-1 border rounded px-2 py-1"
                            />
                            <button
                                type="button"
                                @click="removeObjectif(index)"
                                class="text-red-600 hover:text-red-800"
                            >
                                ✕
                            </button>
                        </div>
                        <button
                            type="button"
                            @click="addObjectif"
                            class="mt-2 px-3 py-1 bg-blue-100 text-blue-700 rounded"
                        >
                            + Ajouter un objectif
                        </button>
                    </div>
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
                    class="px-4 py-2 bg-gray-300 rounded"
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
        formation: Object,
        saveUrl: String,
        deleteUrl: String,
        csrfToken: String,
        isAdmin: Boolean,
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
        removeModalite(index) {
            this.form.modalites.splice(index, 1);
        },
        addObjectif() {
            this.form.objectifs = this.form.objectifs || [];
            this.form.objectifs.push("");
        },
        removeObjectif(index) {
            this.form.objectifs.splice(index, 1);
        },
        async saveChanges() {
            this.saving = true;
            this.error = "";
            this.success = "";
            try {
                const payload = {
                    ...this.form,
                };
                const res = await fetch(this.saveUrl, {
                    method: "PATCH",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(payload),
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
            if (
                !confirm("Êtes-vous sûr de vouloir supprimer cette formation ?")
            ) {
                e.preventDefault();
            }
        },
        formatDate(dateStr) {
            if (!dateStr) return "";
            const d = new Date(dateStr);
            return (
                d.toLocaleDateString("fr-FR") +
                " " +
                d.toLocaleTimeString("fr-FR")
            );
        },
        cancelEdit() {
            this.editing = false;
            this.form = { ...this.formation };
        },
        async saveChanges() {
            this.saving = true;
            this.error = "";
            this.success = "";
            try {
                const payload = {
                    ...this.form,
                };
                const res = await fetch(this.saveUrl, {
                    method: "PATCH",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(payload),
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
            if (
                !confirm("Êtes-vous sûr de vouloir supprimer cette formation ?")
            ) {
                e.preventDefault();
            }
        },
        formatDate(dateStr) {
            if (!dateStr) return "";
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
