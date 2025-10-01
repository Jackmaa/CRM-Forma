// assets/vue/utils/auth.js

/**
 * POST /api/login { email, password } -> { token }
 * Stocke le token dans localStorage
 */
export async function apiLogin(email, password) {
    const res = await fetch("/api/login", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email, password }),
    });

    if (!res.ok) {
        let msg = "Échec connexion";
        try {
            const e = await res.json();
            msg = e.message || msg;
        } catch {}
        throw new Error(msg);
    }

    const data = await res.json();
    const token = data.token;
    if (!token) throw new Error("Token JWT manquant dans la réponse");

    localStorage.setItem("jwt_token", token);
    window.dispatchEvent(new Event("auth:changed")); // ⬅️ notifie l’app
    return token;
}

export function apiLogout() {
    localStorage.removeItem("jwt_token");
    fetch("/logout", { method: "POST", credentials: "include" }).catch(
        () => {}
    );
    window.dispatchEvent(new Event("auth:changed"));
}
