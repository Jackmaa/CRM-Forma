<template>
    <div class="space-y-6">
        <!-- 🚀 Hero Bar -->
        <div
            class="flex flex-col md:flex-row md:justify-between items-start md:items-center bg-base-100 p-4 rounded-lg shadow"
        >
            <h1 class="text-3xl font-bold text-base-content">
                {{ formation.titre }}
            </h1>
            <div class="mt-3 md:mt-0 flex gap-2">
                <!-- Affichage / Edition -->
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
                <SectionCard icon="BookOpen" title="Résumé">
                    <InfoRow label="Thématique" :value="formation.thematique" />
                    <InfoRow label="Niveau" :value="formation.niveau" />
                    <InfoRow label="Durée" :value="formation.duree + ' h'" />
                    <InfoRow label="Tarif" :value="formation.tarif + ' €'" />
                    <InfoRow
                        label="Créé le"
                        :value="formatDate(formation.createdAt)"
                    />
                    <InfoRow
                        v-if="formation.responsable"
                        label="Responsable"
                        :value="formation.responsable"
                    />
                </SectionCard>

                <SectionCard icon="List" title="Modalités">
                    <ul class="list-disc list-inside space-y-1">
                        <li
                            v-for="(m, i) in formation.modalites"
                            :key="i"
                            class="text-base-content"
                        >
                            {{ m }}
                        </li>
                    </ul>
                </SectionCard>

                <SectionCard icon="CheckCircle" title="Objectifs">
                    <ul class="list-disc list-inside space-y-1">
                        <li
                            v-for="(o, i) in formation.objectifs"
                            :key="i"
                            class="text-base-content"
                        >
                            {{ o }}
                        </li>
                    </ul>
                </SectionCard>
            </div>

            <!-- 🖊️ Détail ou Formulaire (colonne de droite) -->
            <div class="lg:col-span-2">
                <!-- Vue simple -->
                <template v-if="!editing">
                    <SectionCard
                        icon="Clipboard"
                        title="Prérequis & Description"
                    >
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <h3 class="font-medium text-base-content mb-1">
                                    Prérequis
                                </h3>
                                <div
                                    class="p-4 bg-base-200 rounded whitespace-pre-line"
                                >
                                    {{ formation.prerequis }}
                                </div>
                            </div>
                            <div>
                                <h3 class="font-medium text-base-content mb-1">
                                    Description
                                </h3>
                                <div
                                    class="p-4 bg-base-200 rounded whitespace-pre-line"
                                >
                                    {{ formation.description }}
                                </div>
                            </div>
                        </div>
                    </SectionCard>
                </template>

                <!-- Formulaire d'édition -->
                <template v-else>
                    <form @submit.prevent="saveChanges" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <FormControl label="Titre" :error="errors.titre">
                                <input
                                    v-model="form.titre"
                                    class="input input-bordered w-full"
                                />
                            </FormControl>
                            <FormControl
                                label="Thématique"
                                :error="errors.thematique"
                            >
                                <input
                                    v-model="form.thematique"
                                    class="input input-bordered w-full"
                                />
                            </FormControl>
                            <FormControl label="Niveau" :error="errors.niveau">
                                <input
                                    v-model="form.niveau"
                                    class="input input-bordered w-full"
                                />
                            </FormControl>
                            <FormControl
                                label="Durée (h)"
                                :error="errors.duree"
                            >
                                <input
                                    v-model.number="form.duree"
                                    type="number"
                                    class="input input-bordered w-full"
                                />
                            </FormControl>
                            <FormControl
                                label="Tarif (€)"
                                :error="errors.tarif"
                            >
                                <input
                                    v-model.number="form.tarif"
                                    type="number"
                                    class="input input-bordered w-full"
                                />
                            </FormControl>
                            <FormControl
                                label="Prérequis"
                                :error="errors.prerequis"
                                class="md:col-span-2"
                            >
                                <textarea
                                    v-model="form.prerequis"
                                    rows="3"
                                    class="textarea textarea-bordered w-full"
                                />
                            </FormControl>
                            <FormControl
                                label="Description"
                                :error="errors.description"
                                class="md:col-span-2"
                            >
                                <textarea
                                    v-model="form.description"
                                    rows="4"
                                    class="textarea textarea-bordered w-full"
                                />
                            </FormControl>
                        </div>

                        <!-- Modalités dynamiques -->
                        <SectionCard icon="List" title="Modalités pédagogiques">
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
                        </SectionCard>

                        <!-- Objectifs dynamiques -->
                        <SectionCard
                            icon="CheckCircle"
                            title="Objectifs pédagogiques"
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
                        </SectionCard>

                        <p v-if="errorGeneral" class="text-error">
                            {{ errorGeneral }}
                        </p>
                    </form>
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
            this.errors = {};
            this.errorGeneral = "";

            const res = await fetch(this.saveUrl, {
                method: "PATCH",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(this.form),
            });

            if (res.ok) {
                this.success = "Modifications enregistrées.";
                this.editing = false;
            } else if (res.status === 400) {
                const json = await res.json();
                this.errors = json.violations || {};
                this.errorGeneral = json.message || "";
            } else {
                this.errorGeneral = "Une erreur est survenue.";
            }
            this.saving = false;
        },
        confirmDelete(e) {
            if (!confirm("Confirmer la suppression ?")) e.preventDefault();
        },
        formatDate(d) {
            if (!d) return "";
            const dt = new Date(d);
            return dt.toLocaleString("fr-FR");
        },
    },
};
</script>

<style scoped>
/* DaisyUI + Tailwind gèrent tout le style */
</style>
