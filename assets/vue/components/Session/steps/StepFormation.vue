<template>
    <div>
        <label class="block mb-1 font-medium">Choisir la formation</label>
        <select
            v-model="local.formationId"
            class="w-full border rounded px-2 py-1 text-black"
        >
            <option :value="null" disabled>-- Sélectionnez --</option>
            <option v-for="f in formations" :key="f.id" :value="f.id">
                {{ f.title }}
            </option>
        </select>
    </div>
</template>

<script setup>
import { ref, onMounted, watch, toRefs } from "vue";

const props = defineProps({
    formationId: Number,
});
const emit = defineEmits(["update"]);

const local = ref({ formationId: props.formationId });
const formations = ref([]);

onMounted(async () => {
    formations.value = await fetch("/formation/api").then((r) => r.json());
});

// Émettre les modifications vers le parent
watch(
    () => local.value,
    (val) => emit("update", { formationId: val.formationId }),
    { deep: true, immediate: true }
);
</script>
