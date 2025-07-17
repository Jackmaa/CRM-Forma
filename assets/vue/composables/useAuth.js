// assets/vue/composables/useAuth.js
import { computed } from "vue";

export function useAuth() {
    // on lit la variable initialisée dans votre <head> Twig
    const roles = window.APP_USER_ROLES || [];

    const isStagiaire = computed(() => roles.includes("ROLE_STAGIAIRE"));
    const isAdmin = computed(() => roles.includes("ROLE_ADMIN_CENTRE"));

    return { roles, isStagiaire, isAdmin };
}
