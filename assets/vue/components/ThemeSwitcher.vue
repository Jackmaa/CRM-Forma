<template>
    <div class="flex items-center space-x-2">
        <!-- Icône Soleil pour indiquer le mode clair -->
        <SunIcon class="w-5 h-5 text-yellow-500" aria-label="Mode clair" />

        <!-- Interrupteur personnalisé -->
        <label
            class="relative inline-flex items-center mx-2 cursor-pointer transition-transform hover:scale-105 active:scale-95"
        >
            <!-- Case à cocher cachée servant de contrôleur -->
            <input
                type="checkbox"
                class="sr-only peer"
                :checked="theme === 'dark'"
                @change="toggleTheme"
            />
            <!-- Piste (track) de l'interrupteur -->
            <div
                class="w-14 h-7 bg-gray-300 rounded-full peer-focus:ring-2 peer-focus:ring-primary dark:peer-focus:ring-secondary transition-colors peer-checked:bg-primary peer-checked:bg-opacity-80"
            ></div>
            <!-- Bouton (knob) se déplaçant lorsque l'état change -->
            <div
                class="absolute left-0.5 top-0.5 bg-white w-6 h-6 rounded-full shadow-md transition-transform transform peer-checked:translate-x-7 peer-checked:bg-white peer-focus:ring-2 peer-focus:ring-offset-2 peer-focus:ring-primary active:scale-110"
            ></div>
        </label>

        <!-- Icône Lune pour indiquer le mode sombre -->
        <MoonIcon class="w-5 h-5 text-gray-800" aria-label="Mode sombre" />
    </div>
</template>

<script setup>
// Import du composable pour gérer le thème
import { useTheme } from "../composables/useTheme";
// Import des icônes Soleil et Lune depuis lucide-vue-next
import { Sun as SunIcon, Moon as MoonIcon } from "lucide-vue-next";

// Récupération du thème courant et de la fonction pour le définir
const { theme, setTheme } = useTheme();

/**
 * Change le thème: si le thème actuel est sombre, passe en clair, et inversement.
 */
function toggleTheme() {
    setTheme(theme.value === "dark" ? "light" : "dark");
}
</script>

<style scoped>
/*
    Pas de styles supplémentaires :
    la mise en forme et les animations sont gérées via les utilitaires Tailwind CSS et les classes `peer`.
  */
</style>
