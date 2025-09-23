// assets/vue/composables/useAuth.js
export function useAuth() {
    // on lit la variable initialisée dans le <head> Twig
    const roles = Array.isArray(window.APP_USER_ROLES)
        ? window.APP_USER_ROLES
        : [];
    const has = (r) => roles.includes(r);

    return {
        roles,
        isStagiaire: has("ROLE_STAGIAIRE"),
        // tolérant: accepte ROLE_ADMIN_CENTRE ou ROLE_ADMIN
        isAdmin: has("ROLE_ADMIN_CENTRE") || has("ROLE_ADMIN"),
    };
}
