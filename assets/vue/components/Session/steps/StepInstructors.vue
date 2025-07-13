<template>
    <div class="form-control w-full">
        <label class="label">
            <span class="label-text text-base-content">Formateurs</span>
        </label>
        <select
            v-model="local.formateurIds"
            multiple
            class="select select-bordered w-full h-32"
        >
            <option
                v-for="instructor in instructors"
                :key="instructor.id"
                :value="instructor.id"
            >
                {{
                    instructor.fullname ||
                    `${instructor.prenom} ${instructor.nom}`
                }}
            </option>
        </select>
        <p v-if="error" class="text-error mt-1">{{ error }}</p>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from "vue";

const props = defineProps({
    formateurIds: {
        type: Array,
        default: () => [],
    },
});
const emit = defineEmits(["update"]);

// Local copy of the selected IDs
const local = reactive({ formateurIds: [...props.formateurIds] });

// List of available instructors
const instructors = ref([]);
const error = ref("");

onMounted(async () => {
    try {
        const res = await fetch("/api/users?role=FORMATEUR");
        if (!res.ok) throw new Error("Échec du chargement des formateurs");
        instructors.value = await res.json();
    } catch (e) {
        error.value = e.message;
    }
});

// Émettre les modifications vers le parent
watch(
    () => local.formateurIds,
    (val) => emit("update", { formateurIds: val }),
    { deep: true, immediate: true }
);
</script>

<style scoped>
/* DaisyUI gère le style, pas de CSS custom nécessaire */
</style>
