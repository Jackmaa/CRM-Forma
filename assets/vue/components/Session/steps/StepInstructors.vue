<template>
    <div class="space-y-2">
        <label class="block text-sm font-medium text-gray-700"
            >Formateurs</label
        >
        <select
            v-model="local.formateurIds"
            multiple
            class="mt-1 block w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring focus:border-blue-300 h-32"
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
        <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
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
const local = reactive({
    formateurIds: [...props.formateurIds],
});

// List of available instructors
const instructors = ref([]);
const error = ref("");

onMounted(async () => {
    try {
        const res = await fetch("/api/users?role=FORMATEUR");
        if (!res.ok) throw new Error("Échec du chargement des formateurs");
        const data = await res.json();
        instructors.value = data;
    } catch (e) {
        error.value = e.message;
    }
});

// Émmettre les modifications vers le parent
watch(
    () => local.formateurIds,
    (val) => {
        emit("update", { formateurIds: val });
    },
    { deep: true, immediate: true }
);
</script>
