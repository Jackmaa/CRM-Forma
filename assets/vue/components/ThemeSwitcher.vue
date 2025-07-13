<template>
    <div class="flex items-center space-x-2">
        <!-- Sun Icon -->
        <SunIcon class="w-5 h-5 text-yellow-500" aria-label="Mode clair" />

        <!-- Custom toggle switch -->
        <label
            class="relative inline-flex items-center mx-2 cursor-pointer transition-transform hover:scale-105 active:scale-95"
        >
            <input
                type="checkbox"
                class="sr-only peer"
                :checked="theme === 'dark'"
                @change="toggleTheme"
            />
            <!-- Background track -->
            <div
                class="w-14 h-7 bg-gray-300 rounded-full peer-focus:ring-2 peer-focus:ring-primary dark:peer-focus:ring-secondary transition-colors peer-checked:bg-primary peer-checked:bg-opacity-80"
            ></div>
            <!-- Knob -->
            <div
                class="absolute left-0.5 top-0.5 bg-white w-6 h-6 rounded-full shadow-md transition-transform transform peer-checked:translate-x-7 peer-checked:bg-white peer-focus:ring-2 peer-focus:ring-offset-2 peer-focus:ring-primary active:scale-110"
            ></div>
        </label>

        <!-- Moon Icon -->
        <MoonIcon class="w-5 h-5 text-gray-800" aria-label="Mode sombre" />
    </div>
</template>

<script setup>
import { useTheme } from "../composables/useTheme";
import { Sun as SunIcon, Moon as MoonIcon } from "lucide-vue-next";

const { theme, setTheme } = useTheme();

/**
 * Basculer entre les thèmes clair et sombre
 */
function toggleTheme() {
    setTheme(theme.value === "dark" ? "light" : "dark");
}
</script>

<style scoped>
/* No extra styling; using Tailwind peer utilities */
</style>
