<template>
    <section class="space-y-6">
        <ToastContainer class="fixed top-1 right-1 z-50" />

        <!-- Hero Bar -->
        <div
            class="flex flex-col md:flex-row md:justify-between items-start md:items-center bg-base-100 p-4 rounded-lg shadow"
        >
            <h1 class="text-3xl font-bold text-base-content">
                {{ formation.titre }}
            </h1>
            <div class="mt-3 md:mt-0 flex gap-2">
                <template v-if="!editing">
                    <!-- Caché pour les stagiaires -->
                    <button
                        v-if="isAdmin"
                        @click="startEdit"
                        class="btn btn-outline btn-sm"
                    >
                        ✏️ Éditer
                    </button>
                    <form
                        v-if="isAdmin"
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
            <!-- 📝 Résumé -->
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

            <!-- 🖊️ Détail / Formulaire -->
            <div class="lg:col-span-2">
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
    </section>
</template>

<script setup>
import { ref } from "vue";
import { toast } from "@/composables/useToast";
import { useAuth } from "@/composables/useAuth";
import InfoRow from "@/components/InfoRow.vue";
import SectionCard from "@/components/SectionCard.vue";
import FormControl from "@/components/FormControl.vue";
import ToastContainer from "@/components/ToastContainer.vue";

const { isAdmin } = useAuth();

const props = defineProps({
    formation: { type: Object, required: true },
    saveUrl: { type: String, required: true },
    deleteUrl: { type: String, required: true },
    csrfToken: { type: String, required: true },
});

// État local
const editing = ref(false);
const form = ref({ ...props.formation });
const errors = ref({});
const errorGeneral = ref("");
const saving = ref(false);

function startEdit() {
    editing.value = true;
    errors.value = {};
    errorGeneral.value = "";
}

function cancelEdit() {
    editing.value = false;
    form.value = { ...props.formation };
    errors.value = {};
    errorGeneral.value = "";
}

function addModalite() {
    form.value.modalites = form.value.modalites || [];
    form.value.modalites.push("");
}

function removeModalite(i) {
    form.value.modalites.splice(i, 1);
}

function addObjectif() {
    form.value.objectifs = form.value.objectifs || [];
    form.value.objectifs.push("");
}

function removeObjectif(i) {
    form.value.objectifs.splice(i, 1);
}

async function saveChanges() {
    saving.value = true;
    errors.value = {};
    errorGeneral.value = "";
    try {
        const res = await fetch(props.saveUrl, {
            method: "PATCH",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(form.value),
        });
        if (res.ok) {
            toast.success("Formation mise à jour !");
            editing.value = false;
            Object.assign(props.formation, form.value);
        } else if (res.status === 400) {
            const json = await res.json();
            errors.value = json.violations || {};
            errorGeneral.value = json.message || "";
            toast.error("Erreur de validation");
        } else {
            throw new Error();
        }
    } catch {
        errorGeneral.value = "Une erreur est survenue.";
        toast.error("Échec de la mise à jour");
    } finally {
        saving.value = false;
    }
}

function confirmDelete(e) {
    if (!confirm("Confirmer la suppression ?")) e.preventDefault();
}

function formatDate(d) {
    return d ? new Date(d).toLocaleString("fr-FR") : "";
}
</script>

<style scoped>
/* Style géré par Tailwind + DaisyUI */
</style>
