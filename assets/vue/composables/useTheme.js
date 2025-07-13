// assets/vue/composables/useTheme.js

import { ref, watch } from "vue";

// Cette variable vit au module–scope : tous les appels à useTheme() la partagent
const theme = ref(localStorage.getItem("theme") || "light");

// fonction interne pour appliquer le data-theme
function applyTheme(val) {
    document.documentElement.setAttribute("data-theme", val);
}

// applique immédiatement une seule fois
applyTheme(theme.value);

// surveille une seule fois
watch(theme, (newVal) => {
    localStorage.setItem("theme", newVal);
    applyTheme(newVal);
});

export function useTheme() {
    // chaque composant qui appelle useTheme() obtient le même ref & setter
    return {
        theme,
        setTheme: (val) => (theme.value = val),
    };
}
