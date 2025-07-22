<template>
    <section class="card bg-base-100 shadow-lg p-6 space-y-6 relative">
        <ToastContainer class="fixed top-1 right-1 z-50" />

        <!-- 🚀 Hero Bar -->
        <div
            class="flex flex-col md:flex-row md:justify-between items-start md:items-center bg-base-100 p-4 rounded-lg shadow"
        >
            <h1 class="text-3xl font-bold text-base-content">
                {{ user.prenom }} {{ user.nom }}
            </h1>
            <div class="mt-3 md:mt-0 flex gap-2">
                <template v-if="!editing">
                    <!-- boutons Éditer/Supprimer : visibles uniquement aux admins -->
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
            <!-- 📝 Résumé (colonne de gauche) -->
            <div class="lg:col-span-1 space-y-4">
                <SectionCard icon="User" title="Détails utilisateur">
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
                <form
                    v-if="editing"
                    @submit.prevent="saveChanges"
                    class="space-y-6"
                >
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
                        <FormControl label="Compte actif" class="md:col-span-2">
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
            </div>
        </div>
    </section>
</template>

<script>
/**
 * Composant d'affichage détaillé d'un utilisateur.
 *
 * Affiche les informations, permet l'édition (admin) et la suppression.
 *
 * Props :
 * - user (Object) : Données de l'utilisateur.
 * - saveUrl (String) : URL d'API pour sauvegarder les modifications.
 * - deleteUrl (String) : URL d'API pour supprimer l'utilisateur.
 * - csrfToken (String) : Jeton CSRF pour la suppression.
 *
 * État local :
 * - editing : mode édition activé ou non
 * - form : copie locale de l'utilisateur pour édition
 * - errors : erreurs de validation
 * - errorGeneral : erreur générale
 * - saving : état de sauvegarde
 */
import { ref, reactive } from "vue";
import { toast } from "@/composables/useToast";
import { useAuth } from "@/composables/useAuth";
import ToastContainer from "@/components/ToastContainer.vue";
import InfoRow from "@/components/InfoRow.vue";
import SectionCard from "@/components/SectionCard.vue";
import FormControl from "@/components/FormControl.vue";

export default {
    components: { ToastContainer, InfoRow, SectionCard, FormControl },
    props: {
        user: Object,
        saveUrl: String,
        deleteUrl: String,
        csrfToken: String,
    },
    setup(props) {
        const { isAdmin } = useAuth();

        const editing = ref(false);
        const saving = ref(false);
        const errors = reactive({});
        const errorGeneral = ref("");
        const form = reactive({ ...props.user, password: "" });

        function startEdit() {
            if (!isAdmin.value) return;
            editing.value = true;
            Object.keys(errors).forEach((k) => delete errors[k]);
            errorGeneral.value = "";
        }
        function cancelEdit() {
            editing.value = false;
            Object.assign(form, { ...props.user, password: "" });
            Object.keys(errors).forEach((k) => delete errors[k]);
            errorGeneral.value = "";
        }
        async function saveChanges() {
            saving.value = true;
            Object.keys(errors).forEach((k) => delete errors[k]);
            errorGeneral.value = "";

            const payload = { ...form };
            if (!payload.password) delete payload.password;

            const res = await fetch(props.saveUrl, {
                method: "PATCH",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload),
            });

            if (res.ok) {
                toast.success("Utilisateur mis à jour !"); // ← success toast
                editing.value = false;
                Object.assign(props.user, payload);
            } else if (res.status === 400) {
                const json = await res.json();
                Object.assign(errors, json.violations || {});
                errorGeneral.value = json.message || "";
                toast.error("Erreur de validation"); // ← validation toast
            } else {
                errorGeneral.value = "Une erreur est survenue.";
                toast.error("Échec de la mise à jour"); // ← general error
            }

            saving.value = false;
        }
        function confirmDelete(e) {
            if (!isAdmin.value || !confirm("Confirmer la suppression ?"))
                e.preventDefault();
        }
        function roleLabel(v) {
            return (
                {
                    ADMIN_CENTRE: "Administrateur Centre",
                    FORMATEUR: "Formateur",
                    STAGIAIRE: "Stagiaire",
                    ASSISTANT: "Assistant",
                }[v] || v
            );
        }

        return {
            isAdmin,
            editing,
            form,
            errors,
            errorGeneral,
            saving,
            startEdit,
            cancelEdit,
            saveChanges,
            confirmDelete,
            roleLabel,
        };
    },
};
</script>

<style scoped>
/* Tailwind + DaisyUI handle styling */
</style>
